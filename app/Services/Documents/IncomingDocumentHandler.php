<?php

namespace App\Services\Documents;

use App\Models\Journal\JournalAttachedOrgs;

class IncomingDocumentHandler implements DocumentHandlerInterface
{
    public function handle($document, $journal, $request)
    {
        $journal->update(['dep_j_id' => $request->department_journal]);

        if ($request->doc_org){
            $journalAttachedOrg = new JournalAttachedOrgs();
            $journalAttachedOrg->j_id = $journal->id;
            $journalAttachedOrg->doc_org_id = $request->doc_org;
            $journalAttachedOrg->save();
        }
    }

}
