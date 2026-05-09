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
     * Approve an Action Matrix and forward to PO.
     */
    public function approveMatrix(string $acmId, ?string $remarks, string $toEmpId, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $toEmpId, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();

            // 1. Update Master
            $master->update([
                'status' => 'PO_REVIEW',
                'current_desk_emp_id' => $toEmpId,
                'po_inbox' => 'Y',
                'is_editable_by_po' => 'Y',
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            // 2. Insert Movement
            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $toEmpId,
                'action_type' => 'APPROVED_AND_FORWARDED_TO_PO',
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
    /**
     * Store a comment with optional file attachments.
     */
    public function storeComment(array $data, \App\Models\User $user): void
    {
        DB::transaction(function () use ($data, $user) {
            $acmId = $data['acm_id'];
            
            // 1. Calculate next SL for comments
            $nextSl = DB::table('acm_comments')->where('acm_id', $acmId)->max('sl') + 1;

            // 2. Insert Comment
            DB::table('acm_comments')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'comment_source' => $user->emp_type, // PKSF or PO
                'comment_short' => $data['comment_short'] ?? null,
                'comment_detail' => $data['comment_detail'],
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);

            // 3. Handle File Attachments (Up to 3)
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $index => $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('acm_comments/' . $acmId, $fileName, 'public');

                        DB::table('acm_comments_file_attachment')->insert([
                            'acm_id' => $acmId,
                            'sl' => $nextSl,
                            'file_id' => $index + 1,
                            'file_upload_by' => $user->emp_type,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_type' => $file->getClientMimeType(),
                            'file_size' => $file->getSize(),
                            'created_by' => $user->emp_id,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        });
    }

    /**
     * PO Officer forwards to PO Supervisor.
     */
    public function forwardToPoSupervisor(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            $supervisorId = $user->supervisor_emp_id;

            if (!$supervisorId) {
                throw new \Exception('No PO supervisor assigned to your profile.');
            }

            $master->update([
                'status' => 'PO_SUBMITTED',
                'current_desk_emp_id' => $supervisorId,
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_po_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_po_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $supervisorId,
                'action_type' => 'FORWARDED_TO_SUPERVISOR',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * PO Supervisor approves and sends back to PKSF.
     */
    public function approvePoResponse(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            $pksfCreatorId = $master->created_by;

            $master->update([
                'status' => 'PO_APPROVED',
                'current_desk_emp_id' => $pksfCreatorId,
                'po_inbox' => 'N',
                'is_editable_by_po' => 'N',
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_po_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_po_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $pksfCreatorId,
                'action_type' => 'APPROVED_AND_SENT_TO_PKSF',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * PO Supervisor rejects and sends back to PO Officer.
     */
    public function rejectPoResponse(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            
            // Find the last person who sent it (PO Concern Officer)
            $lastMovement = DB::table('acm_po_movements')
                ->where('acm_id', $acmId)
                ->orderBy('sl', 'desc')
                ->first();
                
            $targetOfficer = $lastMovement ? $lastMovement->from_emp_id : $master->created_by;

            $master->update([
                'status' => 'PO_REJECTED',
                'current_desk_emp_id' => $targetOfficer,
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_po_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_po_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $targetOfficer,
                'action_type' => 'REJECTED_BY_SUPERVISOR',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }
}
