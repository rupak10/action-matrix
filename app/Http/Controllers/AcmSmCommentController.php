<?php

namespace App\Http\Controllers;

use App\Models\AcmMaster;
use App\Models\AcmSmComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcmSmCommentController extends Controller
{
    public function store(Request $request, string $acmId)
    {
        $user   = auth()->user();
        $master = AcmMaster::where('acm_id', $acmId)->firstOrFail();

        // Verify SM user has access to this observation
        abort_unless(
            !in_array($master->status, ['SAVED', 'SUBMITTED', 'REJECTED'])
            && ($user->isSmMd() || in_array($master->po_code, $user->assignedPoCodes())),
            403
        );

        $request->validate([
            'sm_comment'    => 'required|string|max:5000',
            'sm_attachments'   => 'nullable|array|max:3',
            'sm_attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:30720',
        ]);

        $attachments = [];
        if ($request->hasFile('sm_attachments')) {
            foreach ($request->file('sm_attachments') as $file) {
                $path = $file->store("sm_comments/{$acmId}", 'public');
                $attachments[] = [
                    'path'      => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientMimeType(),
                ];
            }
        }

        AcmSmComment::create([
            'acm_id'      => $acmId,
            'emp_id'      => $user->emp_id,
            'comment'     => $request->input('sm_comment'),
            'attachments' => $attachments ?: null,
        ]);

        return back()->with('success', 'Your comment has been saved.');
    }
}
