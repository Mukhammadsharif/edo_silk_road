@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header.css') }}">

@section('content')
    <div class="work-view-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-text"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.inbox') }}</h1>
                        <p>{{ __('message.view_document') ?? 'Просмотр документа' }}</p>
                    </div>
                </div>
                <div class="header-center">
                    @include('templates.alert')
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <a href="{{ route('office.inbox-docs') }}" class="breadcrumb-item">
                            <span>{{ __('title.inbox') }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('menu.view') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <div class="document-view-layout">
            <!-- Left Sidebar -->
            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <!-- Document Content -->
            <div class="document-content-section">
                <div class="work-doc-page">
                    <div class="work-doc-subpage">
                        <div class="header-title">
                            <i data-lucide="file-text"></i>
                            <h3 style="color: #0e0c0c">{{ __('field.doc_text') }}</h3>
                        </div>
                        <div class="printPage" id="printPage">
                            <div class="doc-text mt-3">{!! $document->doc_text??'' !!}</div>
                            @forelse($documentFiles as $key => $file)
                                @switch(strtolower($file->file_ext))
                                    @case('jpg')
                                    @case('jpeg')
                                    @case('png')
                                    @case('pdf')
                                        <div class="document-file-viewer">
                                            <div class="file-name-badge">
                                                <i data-lucide="file"></i>
                                                <span>{{ $file->file_name }}</span>
                                            </div>
                                            <iframe src="{{ route('file.pre-view',['id'=>$file->id]) }}"
                                                    class="document-iframe"></iframe>
                                        </div>
                                        @break
                                @endswitch
                            @empty
                                <div class="empty-document-state">
                                    <i data-lucide="file-x"></i>
                                    <p>{{ __('message.no_document_files') ?? 'Файлы документа не найдены' }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="view-sidebar view-sidebar-right">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-right')
                </div>
            </div>
        </div>

    </div>

    @include('modals.delete-document')
    @include('office.journal.view-js')
    @include('print.print')
    @include('qr-code-doc.qr-code-view-js')
    @include('print.iframe-print')
    @include('e-imzo.e-imzo')

    <script>
        // Initialize lucide icons
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.openConfirmForm').on('click', function () {
                $('.confirmForm').show();
                let btn_id = $(this).data('id');
                let confirm_type = $(this).html();
                let modal_bg_class = "btn-success";
                if (btn_id === -5) {
                    confirm_type = "{{ __('cancel') }}";
                    modal_bg_class = "btn-outline-warning";
                } else if (btn_id === -6) {
                    confirm_type = "{{ __('re-editing') }}";
                    modal_bg_class = "btn-outline-warning";
                }
                $('.modal-bg-class').removeClass().addClass(`btn-modern modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val();
            });
        });
    </script>
@endsection
