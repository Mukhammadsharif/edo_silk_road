<div class="work-doc-subpage" id="pdf3">
    <div id="printPage" class="printPage">
        <!-- Document Header with Logo/Banner -->
        <div class="document-header-modern">
            <div class="header-banner">
                <img src="{{ $sentDocImage }}" alt="Organization Banner" class="banner-image">
            </div>
            <div class="document-meta">
                <div class="doc-number">№ {{ $document->doc_number }}</div>
                <div class="doc-date">{{ date('d.m.Y', strtotime($document->doc_date)) }}</div>
            </div>
        </div>

        <!-- Document Body -->
        <div class="doc-text" id="pdf4">
            @if($html_doc)
                {!! $html_doc !!}
            @endif
        </div>

        <!-- Signature Section -->
        <div class="signature-section-modern">
            <div class="signature-row">
                <div class="signature-position">
                    <span class="position-label">Lavozim:</span>
                    <span
                        class="position-value">{{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}</span>
                </div>
                <div class="signature-qr">
                    <div class="qr-code-wrapper">
                        {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!}
                    </div>
                </div>
                <div class="signature-name">
                    <span
                        class="name-value">{{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??'' }}</span>
                </div>
            </div>
        </div>

        <!-- Performer Section -->
        <div class="performer-section-modern">
            <div class="performer-row">
                <div class="performer-info">
                        <span class="performer-label">
                            @switch($document->doc_lang)
                                @case('uz')
                                    Ijrochi:
                                    @break
                                @case('kr')
                                    Ижрочи:
                                    @break
                                @case('ru')
                                    Исполнитель:
                                    @break
                                @case('en')
                                    Performer:
                                    @break
                            @endswitch
                        </span>
                    <span
                        class="performer-name">{{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/templates/sent-doc-body.css') }}">
