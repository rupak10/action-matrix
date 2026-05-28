<?php

namespace App\Services;

use App\Models\AcmMaster;
use App\Models\AcmTracker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
     * Update an editable Action Matrix before it is forwarded or after it is sent back.
     */
    public function updateMaster(string $acmId, array $data, array $files, array $removeAttachmentSl, \App\Models\User $user): AcmMaster
    {
        $acmId = trim($acmId);

        return DB::transaction(function () use ($acmId, $data, $files, $removeAttachmentSl, $user) {
            $master = AcmMaster::with('attachments')->where('acm_id', $acmId)->firstOrFail();

            $removeAttachmentSl = collect($removeAttachmentSl)
                ->map(fn ($sl) => (int) $sl)
                ->filter(fn ($sl) => $sl > 0)
                ->unique()
                ->values()
                ->all();

            $existingAttachments = $master->attachments;
            $removeCount = $existingAttachments
                ->whereIn('sl', $removeAttachmentSl)
                ->count();

            if (($existingAttachments->count() - $removeCount + count($files)) > 3) {
                throw new \Exception('You can keep a maximum of 3 attachments per Action Matrix.');
            }

            if (!empty($removeAttachmentSl)) {
                $attachmentsToDelete = $existingAttachments->whereIn('sl', $removeAttachmentSl);

                foreach ($attachmentsToDelete as $attachment) {
                    if ($attachment->file_path) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }

                    DB::table('acm_master_file_attachment')
                        ->where('acm_id', $acmId)
                        ->where('sl', $attachment->sl)
                        ->delete();
                }
            }

            $master->update([
                'visiting_date' => $data['visiting_date'],
                'observation_category' => $data['observation_category'],
                'visit_type' => $data['visit_type'],
                'visit_category' => $data['visit_category'],
                'letter_issue_date' => $data['letter_issue_date'] ?? null,
                'letter_response_date' => $data['letter_response_date'] ?? null,
                'pksf_observation' => $data['pksf_observation'],
                'direction_to_po' => $data['direction_to_po'],
                'action_matrix' => $data['action_matrix'],
                'priority' => $data['priority'],
                'updated_at' => now(),
                'updated_by' => $user->emp_id,
            ]);

            $nextSl = (int) DB::table('acm_master_file_attachment')
                ->where('acm_id', $acmId)
                ->max('sl');
            $nextSl++;

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $uniqueName = $acmId . '_' . time() . '_' . $nextSl . '.' . $extension;
                $path = $file->storeAs('action_matrix_attachments', $uniqueName, 'public');

                DB::table('acm_master_file_attachment')->insert([
                    'acm_id' => $acmId,
                    'sl' => $nextSl,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'created_by' => $user->emp_id,
                    'created_at' => now(),
                ]);

                $nextSl++;
            }

            return $master->fresh('attachments');
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
            // is_draft: 1 = saved as draft (editable), 0 = submitted to supervisor (locked)
            $isDraft = (isset($data['forward_to_supervisor']) && $data['forward_to_supervisor'] == 1) ? 0 : 1;
            DB::table('acm_comments')->insert([
                'acm_id'         => $acmId,
                'sl'             => $nextSl,
                'comment_source' => $user->emp_type, // PKSF or PO
                'comment_short'  => $data['comment_short'] ?? null,
                'comment_detail' => $data['comment_detail'],
                'is_draft'       => $isDraft,
                'created_by'     => $user->emp_id,
                'created_at'     => now(),
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
     * Update an existing draft comment (same round, PO CO editing before submit).
     */
    public function updateComment(array $data, \App\Models\User $user): void
    {
        DB::transaction(function () use ($data, $user) {
            $acmId   = trim($data['acm_id']);
            $sl      = (int) $data['comment_sl'];
            $isDraft = (isset($data['forward_to_supervisor']) && $data['forward_to_supervisor'] == 1) ? 0 : 1;

            // 1. Update the comment text and draft status
            DB::table('acm_comments')
                ->where('acm_id', $acmId)
                ->where('sl', $sl)
                ->update([
                    'comment_detail' => $data['comment_detail'],
                    'is_draft'       => $isDraft,
                    'updated_by'     => $user->emp_id,
                    'updated_at'     => now(),
                ]);

            // 2. Remove existing attachments that the user flagged for deletion
            $removeIds = $data['remove_file_ids'] ?? [];
            if (!empty($removeIds)) {
                $filesToRemove = DB::table('acm_comments_file_attachment')
                    ->where('acm_id', $acmId)
                    ->where('sl', $sl)
                    ->whereIn('file_id', $removeIds)
                    ->get();

                foreach ($filesToRemove as $file) {
                    Storage::disk('public')->delete($file->file_path);
                }

                DB::table('acm_comments_file_attachment')
                    ->where('acm_id', $acmId)
                    ->where('sl', $sl)
                    ->whereIn('file_id', $removeIds)
                    ->delete();
            }

            // 3. Add newly uploaded files (continuing from current max file_id)
            if (!empty($data['attachments'])) {
                $maxFileId = DB::table('acm_comments_file_attachment')
                    ->where('acm_id', $acmId)
                    ->where('sl', $sl)
                    ->max('file_id') ?? 0;

                foreach ($data['attachments'] as $index => $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('acm_comments/' . $acmId, $fileName, 'public');

                        DB::table('acm_comments_file_attachment')->insert([
                            'acm_id'         => $acmId,
                            'sl'             => $sl,
                            'file_id'        => $maxFileId + $index + 1,
                            'file_upload_by' => $user->emp_type,
                            'file_name'      => $file->getClientOriginalName(),
                            'file_path'      => $filePath,
                            'file_type'      => $file->getClientMimeType(),
                            'file_size'      => $file->getSize(),
                            'created_by'     => $user->emp_id,
                            'created_at'     => now(),
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

            // Lock any draft comment by this PO CO — it is now submitted (not editable).
            // This handles both the modal path (updateComment already set is_draft=0)
            // and the standalone forward button path (which skips updateComment entirely).
            DB::table('acm_comments')
                ->where('acm_id', $acmId)
                ->where('created_by', $user->emp_id)
                ->where('is_draft', 1)
                ->update([
                    'is_draft'   => 0,
                    'updated_by' => $user->emp_id,
                    'updated_at' => now(),
                ]);

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

    /**
     * PKSF Officer requests closure.
     */
    public function requestClosure(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            $supervisorId = $user->supervisor_emp_id;
            if (!$supervisorId) {
                throw new \Exception('No supervisor assigned to your profile. Cannot request closure.');
            }

            $master->update([
                'status' => 'WAITING_FOR_CLOSURE',
                'current_desk_emp_id' => $supervisorId,
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $supervisorId,
                'action_type' => 'CLOSURE_REQUESTED',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * PKSF Officer requests PO revision (sends to PKSF Supervisor first).
     */
    public function requestRevision(array $data, \App\Models\User $user): void
    {
        DB::transaction(function () use ($data, $user) {
            $acmId = trim($data['acm_id']);
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            $supervisorId = $user->supervisor_emp_id;
            if (!$supervisorId) {
                throw new \Exception('No supervisor assigned to your profile. Cannot request revision.');
            }

            // 1. Store the comment
            $this->storeComment($data, $user);

            // 2. Update Master
            $master->update([
                'status' => 'REVISION_REQUESTED',
                'current_desk_emp_id' => $supervisorId,
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            // 3. Log movement
            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $supervisorId,
                'action_type' => 'REVISION_REQUESTED',
                'remarks' => $data['comment_detail'] ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * PKSF Supervisor approves closure.
     */
    public function approveClosure(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();

            $master->update([
                'status' => 'CLOSED',
                'resolution_status' => 'RESOLVED',
                'current_desk_emp_id' => null, // Lock desk control
                'po_inbox' => 'N',
                'is_editable_by_po' => 'N',
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $user->emp_id,
                'action_type' => 'CLOSED',
                'remarks' => $remarks ?? 'Approved and Closed.',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * PKSF Supervisor rejects closure (sends back to PKSF Officer).
     */
    public function rejectClosure(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            $officerId = $master->created_by;

            $master->update([
                'status' => 'PKSF_REJECTED',
                'current_desk_emp_id' => $officerId,
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $officerId,
                'action_type' => 'CLOSURE_REJECTED',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * PKSF Supervisor approves revision and forwards to PO.
     */
    public function approveRevision(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();

            // Find PO Concern Officer
            $poOfficer = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'PO_CO');
            })->where('po_code', $master->po_code)->first();

            if (!$poOfficer) {
                $poOfficer = \App\Models\User::where('po_code', $master->po_code)
                    ->where('emp_type', 'PO')
                    ->first();
            }

            if (!$poOfficer) {
                throw new \Exception('No PO Concern Officer found for PO Code ' . $master->po_code);
            }

            $master->update([
                'status' => 'PO_REVIEW',
                'current_desk_emp_id' => $poOfficer->emp_id,
                'po_inbox' => 'Y',
                'is_editable_by_po' => 'Y',
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $poOfficer->emp_id,
                'action_type' => 'REVISION_APPROVED_AND_SENT_TO_PO',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * PKSF Supervisor rejects revision request (sends back to PKSF Officer).
     */
    public function rejectRevision(string $acmId, ?string $remarks, \App\Models\User $user): void
    {
        $acmId = trim($acmId);
        DB::transaction(function () use ($acmId, $remarks, $user) {
            $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();
            $officerId = $master->created_by;

            $master->update([
                'status' => 'PKSF_REJECTED',
                'current_desk_emp_id' => $officerId,
                'updated_at' => now(),
                'updated_by' => $user->emp_id
            ]);

            $nextSl = DB::table('acm_pksf_movements')->where('acm_id', $acmId)->max('sl') + 1;
            DB::table('acm_pksf_movements')->insert([
                'acm_id' => $acmId,
                'sl' => $nextSl,
                'from_emp_id' => $user->emp_id,
                'to_emp_id' => $officerId,
                'action_type' => 'REVISION_REJECTED',
                'remarks' => $remarks ?? '',
                'created_by' => $user->emp_id,
                'created_at' => now(),
            ]);
        });
    }
}
