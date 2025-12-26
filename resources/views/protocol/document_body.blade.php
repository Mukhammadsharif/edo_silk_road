<div class="work-doc-page">
    <div class="work-doc-subpage">
        <div id="printPage" class="printPage">

            <div class="sent-doc-date-number">
                @if($document->doc_number)
                    № {{ $document->doc_number }}<br>
                @endif
                {{ date('d.m.Y', strtotime($document->doc_date)) }}
            </div>

            <div class="doc-text">
                @if($html_doc)
                    {!! $html_doc !!}
                @endif
            </div>

            {{--@forelse($documentFiles as $key => $file)
                @switch(strtolower($file->file_ext))
                    @case('jpg')
                    @case('jpeg')
                    @case('png')
                    @case('pdf')
                        <span class="iframe-text">{{ $file->file_name }}</span>
                        <iframe class="iframe-content" src="{{ route('prt-document-file.pre-view',['id'=>$file->id]) }}"
                                style="width: 100%; height: 800px;"></iframe>
                        <hr class="iframe-text">
                        @break
                        @break
                @endswitch
            @empty

            @endforelse--}}

            <div class="doc-performer">
                @switch($document->doc_lang)
                    @case('uz')
                        <span>Yaratuvchi:</span>
                        @break
                    @case('kr')
                        <span>Яратувчи:</span>
                        @break
                    @case('ru')
                        <span>Создатель:</span>
                        @break
                    @case('en')
                        <span>Creator:</span>
                        @break
                @endswitch

                @php($secretaryShortName = $document->secretary->personal??0)
                @if($secretaryShortName)
                    {{ $secretaryShortName->shortNameLang($document->doc_lang) }}
                @endif
            </div>

            <div class="doc-tel">
                @switch($document->doc_lang)
                    @case('uz')
                        Telefon raqam:
                        @break
                    @case('kr')
                        Телефон рақам:
                        @break
                    @case('ru')
                        Номер телефона:
                        @break
                    @case('en')
                        Phone number:
                        @break
                @endswitch
                {{ $document->secretary->personal->phone??'-' }}
            </div>

            <div class="doc-qrcode" style="width: 100px">
                <div class="hr-prt-docs-qr text-center">
                    {!! $document->docQrCode($document->id, $document->doc_code) !!}
                    <div class="pt-5">
                        <span class="badge badge-pill {{ $document->statusName->badgeClass->name??'' }}">
                            {{ $document->statusDoc($document->status) }}
                        </span>
                    </div>
                </div>
            </div>

            @include('protocol.document_members')

{{--            @include('protocol.document_hr_emp')--}}

            @include('print.iframe-print')

        </div>
    </div>
</div>
