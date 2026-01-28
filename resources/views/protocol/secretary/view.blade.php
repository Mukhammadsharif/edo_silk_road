@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-left.css') }}">

@section('content')
    <div class="work-view-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="briefcase"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ $document->docType->lang($document->docType->id) }}</h1>
                        <p>{{ __('message.view_work_document') ?? 'Просмотр рабочего документа' }}</p>
                    </div>
                </div>
                <div class="header-center">
                    @include('templates.alert')
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern" aria-label="breadcrumb">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <a href="{{ url('protocol/secretary', [$document->docType->doc_hash]) }}" class="breadcrumb-item">
                            <span>{{ $document->docType->lang($document->docType->id) }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('menu.view') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <div class="document-view-layout">
            @include('protocol.document_info')

            <div class="col-md-6">

                @include('protocol.document_body')

            </div>

            <div class="col-md-4">
                @include('templates.mini_editor_js')

                @include('protocol.document_reply_comments')

                @include('protocol.document_secretary_sign')

                @if($document->status != 1)

                    @include('templates.comment_form')

                @endif

                @include('protocol.document_secretary_actions')

                @include('protocol.document_download_print_actions')
            </div>
        </div>
    </div>
@endsection
