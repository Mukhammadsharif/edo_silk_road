@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ $document->docType->lang($document->docType->id) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-3">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ url('protocol/members', $document->docType->doc_hash??'#') }}">{{ $document->docType->lang($document->docType->id) }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            @include('protocol.document_info')

            <div class="col-md-6">

                @include('protocol.document_body')

            </div>

            <div class="col-md-4">
                @include('templates.mini_editor_js')

                @include('protocol.document_reply_comments')

                @include('protocol.document_members_actions')

                @include('templates.comment_form')

                @include('protocol.document_download_print_actions')
            </div>
        </div>
    </div>
@endsection
