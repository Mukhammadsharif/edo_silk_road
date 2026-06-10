<?php

namespace Database\Seeders;

use App\Models\Admin\AppTemps;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentOrganizations;
use App\Models\ECP\SignatureDocumentType;
use App\Models\Journal\ConfidentialTypes;
use App\Models\Journal\JournalExecutionStepRoles;
use App\Models\Journal\JournalExecutionSteps;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use App\Models\Resolution\ReplyCommentTypes;
use App\Models\Resolution\ResolutionExecutionRoles;
use App\Models\Resolution\ResolutionTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DocumentJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        JournalTypes::truncate();
        $jsonFile = base_path('database/seeders/data/journal_type.json');
        $jsonData = json_decode(File::get($jsonFile), true);
        foreach ($jsonData['journal_types'] as $item) {
            JournalTypes::create([
                'parent_id' => $item['parent_id'],
                'title' => $item['title'],
                'lang_id' => $item['lang_id'],
                'doc_code' => $item['doc_code'],
                'level_number' => $item['level_number'],
                'order_number' => $item['order_number'],
                'status' => $item['status'],
            ]);
        }

        AppTemps::truncate();
        $jsonFile1 = base_path('database/seeders/data/app_temps.json');
        $jsonData1 = json_decode(File::get($jsonFile1), true);
        foreach ($jsonData1['app_temps'] as $item1) {
            AppTemps::create([
                'journal_id' => $item1['journal_id'],
                't_name' => $item1['t_name'],
                't_code' => $item1['t_code'],
                't_text' => $item1['t_text'],
                't_category' => $item1['t_category'],
//                'created_id' => $item1['created_id'],
                'status' => $item1['status'],
            ]);
        }

        JournalExecutionSteps::truncate();
        $jsonFile2 = base_path('database/seeders/data/j_steps.json');
        $jsonData2 = json_decode(File::get($jsonFile2), true);
        foreach ($jsonData2['journal_execution_steps'] as $item2) {
            JournalExecutionSteps::create([
                'journal_type_id' => $item2['journal_type_id'],
                'exec_role_id' => $item2['exec_role_id'],
                'exec_status' => $item2['exec_status']
            ]);
        }

        JournalExecutionStepRoles::truncate();
        $jsonFile3 = base_path('database/seeders/data/j_step_roles.json');
        $jsonData3 = json_decode(File::get($jsonFile3), true);
        foreach ($jsonData3['journal_execution_step_roles'] as $item3) {
            JournalExecutionStepRoles::create([
                'j_exec_step_id' => $item3['j_exec_step_id'],
                'role_id' => $item3['role_id']
            ]);
        }

        JournalTypeControls::truncate();
        $jsonFile4 = base_path('database/seeders/data/j_type_control.json');
        $jsonData4 = json_decode(File::get($jsonFile4), true);
        foreach ($jsonData4['journal_type_controls'] as $item4) {
            JournalTypeControls::create([
                'journal_type_id' => $item4['journal_type_id'],
                'status' => $item4['status']
            ]);
        }

        ResolutionTypes::truncate();
        $jsonFile5 = base_path('database/seeders/data/resol_type.json');
        $jsonData5 = json_decode(File::get($jsonFile5), true);
        foreach ($jsonData5['resolution_types'] as $item5) {
            ResolutionTypes::create([
                'title' => $item5['title'],
                'lang_id' => $item5['lang_id'],
                'doc_code' => $item5['doc_code'],
                'status' => $item5['status']
            ]);
        }

        ResolutionExecutionRoles::truncate();
        $jsonFile6 = base_path('database/seeders/data/resol_exec_role.json');
        $jsonData6 = json_decode(File::get($jsonFile6), true);
        foreach ($jsonData6['resolution_execution_roles'] as $item6) {
            ResolutionExecutionRoles::create([
                'title' => $item6['title'],
                'lang_id' => $item6['lang_id'],
                'doc_code' => $item6['doc_code'],
                'status' => $item6['status']
            ]);
        }

        ConfidentialTypes::truncate();
        $jsonFile7 = base_path('database/seeders/data/doc_conf_type.json');
        $jsonData7 = json_decode(File::get($jsonFile7), true);
        foreach ($jsonData7['confidential_types'] as $item7) {
            ConfidentialTypes::create([
                'title' => $item7['title'],
                'lang_id' => $item7['lang_id'],
                'doc_code' => $item7['doc_code'],
                'status' => $item7['status']
            ]);
        }

        DocumentExecutionRoles::truncate();
        $jsonFile8 = base_path('database/seeders/data/doc_exec_role.json');
        $jsonData8 = json_decode(File::get($jsonFile8), true);
        foreach ($jsonData8['document_execution_roles'] as $item8) {
            DocumentExecutionRoles::create([
                'title' => $item8['title'],
                'lang_id' => $item8['lang_id'],
                'doc_code' => $item8['doc_code'],
                'status' => $item8['status']
            ]);
        }

        DocumentOrganizations::truncate();
        $jsonFile9 = base_path('database/seeders/data/doc_org.json');
        $jsonData9 = json_decode(File::get($jsonFile9), true);
        foreach ($jsonData9['document_organizations'] as $item9) {
            DocumentOrganizations::create([
                'name_uz' => $item9['name_uz'],
                'name_ru' => $item9['name_ru'],
                'name_en' => $item9['name_en'],
                'for_doc_type' => $item9['for_doc_type'],
                'status' => $item9['status']
            ]);
        }

        ReplyCommentTypes::truncate();
        $jsonFile10 = base_path('database/seeders/data/doc_reply_comment_type.json');
        $jsonData10 = json_decode(File::get($jsonFile10), true);
        foreach ($jsonData10['reply_comment_types'] as $item10) {
            ReplyCommentTypes::create([
                'title' => $item10['title'],
                'lang_id' => $item10['lang_id'],
                'doc_code' => $item10['doc_code'],
                'status' => $item10['status']
            ]);
        }

        SignatureDocumentType::truncate();
        $jsonFile11 = base_path('database/seeders/data/sign_doc_type.json');
        $jsonData11 = json_decode(File::get($jsonFile11), true);
        foreach ($jsonData11['signature_document_types'] as $item11) {
            SignatureDocumentType::create([
                'doc_name' => $item11['doc_name'],
                'doc_code' => $item11['doc_code'],
                'status' => $item11['status']
            ]);
        }
    }
}
