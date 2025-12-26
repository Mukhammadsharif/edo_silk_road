<div class="col-md-4">
    <div class="card table-card">

        <div class="card-header">
            <h3>Hujjat haqida ma`lumot</h3>
        </div>
        <div class="card-body feeds-widget">
            <div class="feeds-body">
                <h6 class="title">{{ $journal->journal->title??'' }}</h6>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-flag"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">Hujjat turi:</h6>
                        <p>{{ $journal->journal->title??'' }}</p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-file-text"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">Hujjat nomi:</h6>
                        <p>{{ $document->doc_name }}</p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-clock"></i></div>
                    <div class="feeds-body">
                        @if($journal)
                            @if($journal->journal->doc_code == 'BASE_OUTGOING')
                                <h6 class="title text-blue">Hujjat raqami/sanasi:</h6>
                                <p>№ {{ $journal->fromUser->department->id??'' }}/{{ $document->doc_number }}<br>
                                    {{ date('d.m.Y', strtotime($document->doc_date)) }} yil.
                                </p>
                            @else
                                <h6 class="title text-blue">Hujjat sanasi:</h6>
                                <p>{{ date('d.m.Y', strtotime($document->doc_date)) }} yil.</p>
                            @endif
                        @endif
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-user"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">Kim tomonidan imzolangan:</h6>
                        <p>
                            @if($qrCodeSignUser)
                                {{ mb_substr($qrCodeSignUser->toUser->personal->f_name??'', 0,1,) }}.
                                {{ mb_substr($qrCodeSignUser->toUser->personal->m_name??'', 0,1,) }}.
                                {{ $qrCodeSignUser->toUser->personal->l_name??'' }}
                            @endif
                        </p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-feather"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">Imzolangan sana:</h6>
                        <p>
                            @if($qrCodeSignUser)
                                {{ date('d.m.Y H:i', strtotime($qrCodeSignUser->updated_at)) }}
                            @endif
                        </p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-user"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">Hujjat yaratuvchisi:</h6>
                        <p>
                            {{ mb_substr($journal->fromUser->personal->f_name??'', 0,1,) }}.
                            {{ mb_substr($journal->fromUser->personal->m_name??'', 0,1,) }}.
                            {{ $journal->fromUser->personal->l_name??'' }}
                        </p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
