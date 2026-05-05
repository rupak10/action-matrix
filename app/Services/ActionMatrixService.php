<?php

namespace App\Services;

use App\Models\AcmMaster;
use App\Models\AcmTracker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ActionMatrixService
{
    /**
     * Generate a unique ACM ID based on PO code and serial number.
     * Format: acm-[PO_CODE]-[SL]
     * 
     * @param string $poCode
     * @return string
     */
    public function generateAcmId(string $poCode): string
    {
        return DB::transaction(function () use ($poCode) {
            $tracker = DB::table('acm_tracker')->where('po_code', $poCode)->lockForUpdate()->first();

            if ($tracker) {
                $newSl = $tracker->sl + 1;
                DB::table('acm_tracker')->where('po_code', $poCode)->update(['sl' => $newSl]);
            } else {
                $newSl = 1;
                DB::table('acm_tracker')->insert([
                    'po_code' => $poCode,
                    'sl' => $newSl
                ]);
            }

            return sprintf('ACM-%s-%02d', $poCode, $newSl);
        });
    }

    /**
     * Create a new Action Matrix Master record.
     * 
     * @param array $data
     * @param string $status
     * @param array $files
     * @return AcmMaster
     */
    public function createMaster(array $data, string $status = 'SAVED', array $files = []): AcmMaster
    {
        return DB::transaction(function () use ($data, $status, $files) {
            $acmId = $this->generateAcmId($data['po_code']);
            $user = Auth::user();

            $master = new AcmMaster();
            $master->acm_id = $acmId;
            $master->po_code = $data['po_code'];
            $master->visiting_date = $data['visiting_date'];
            
            // Set internally from logged-in user's department
            $master->observation_dept = $user->dept_name ?? 'N/A';
            
            $master->observation_category = $data['observation_category'];
            $master->visit_type = $data['visit_type'];
            $master->visit_category = $data['visit_category'];
            
            $master->letter_issue_date = $data['letter_issue_date'] ?? null;
            $master->letter_response_date = $data['letter_response_date'] ?? null;
            
            $master->pksf_observation = $data['pksf_observation'];
            $master->direction_to_po = $data['direction_to_po'];
            
            $master->action_matrix = $data['action_matrix'];
            $master->priority = $data['priority'];
            
            // Initial statuses
            $master->status = $status; 
            $master->resolution_status = 'NOT_RESOLVED';
            $master->po_inbox = 'N';
            $master->is_editable_by_po = 'N';
            
            $master->created_by = $user->emp_id;
            $master->current_desk_emp_id = $user->emp_id; // Item starts at the creator's desk
            $master->save();

            // Process File Attachments
            if (!empty($files)) {
                $sl = 1;
                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    
                    // Generate unique filename: ACM-007-01_1680001111_filename.pdf
                    $uniqueName = $acmId . '_' . time() . '_' . $sl . '.' . $extension;
                    
                    // Store file in storage/app/public/action_matrix_attachments
                    $path = $file->storeAs('action_matrix_attachments', $uniqueName, 'public');

                    // Save metadata to AcmMasterFileAttachment
                    $master->attachments()->create([
                        'sl' => $sl,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'created_by' => $user->emp_id
                    ]);
                    
                    $sl++;
                }
            }

            return $master;
        });
    }

    /**
     * Forward an Action Matrix to a supervisor.
     * 
     * @param string $acmId
     * @param string $remarks
     * @param \App\Models\User $user The user initiating the forward
     * @return void
     * @throws \Exception
     */
    public function forwardMatrix(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $supervisorId = $user->supervisor_emp_id;
            if (!$supervisorId) {
                throw new \Exception('You do not have a supervisor assigned. Cannot forward.');
            }

            // 1. Update acm_master directly using Query Builder
            $affected = DB::table('acm_master')
                ->where('acm_id', $acmId)
                ->update([
                    'status' => 'SUBMITTED',
                    'current_desk_emp_id' => $supervisorId,
                    'updated_at' => now(),
                    'updated_by' => $user->emp_id
                ]);

            if ($affected === 0) {
                throw new \Exception('Action Matrix record not found or no changes made.');
            }

            // 2. Calculate next Movement SL
            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;

            // 3. Insert PKSF Movement Record
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $supervisorId,
                'action_type' => 'FORWARDED',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }
    /**
     * Approve an Action Matrix.
     */
    public function approveMatrix(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();

            // 1. Update Master
            $master->update([
                'status' => 'APPROVED',
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            // 2. Insert Movement
            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $user->emp_id, // Stays at supervisor's desk but approved
                'action_type' => 'APPROVED',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Reject (Send Back) an Action Matrix to the creator.
     */
    public function rejectMatrix(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            $creatorId = $master->created_by;

            // 1. Update Master
            $master->update([
                'status' => 'REJECTED',
                'current_desk_emp_id' => $creatorId,
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            // 2. Insert Movement
            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $creatorId,
                'action_type' => 'SENT_BACK',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }
}
