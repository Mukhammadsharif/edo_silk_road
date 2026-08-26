<div class="col-md-4">
    <div class="card table-card">

        <div class="feeds-widget">
            <div class="feeds-body p10">
                <h6 class="text-center">{{ __('field.document_info') }}</h6>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-flag"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">{{ __('field.doc_code') }}</h6>
                        <p>{{ $document->doc_code }}</p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-file-text"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">{{ __('field.doc_type') }}</h6>
                        @php($doc_type = $document->docType->id??'')
                        <span class="badge badge-info text-white">
                            @if($doc_type)
                                {{ $document->docType->lang($doc_type) }}
                            @endif
                    </span>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-file-text"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">{{ __('field.doc_name') }}</h6>
                        <p>{{ $document->doc_name }}</p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i data-lucide="clock"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">{{ __('field.doc_number') }}/{{ __('field.doc_date') }}</h6>
                        <p>№ {{ $document->doc_number }}<br> {{ date('d.m.Y', strtotime($document->doc_date)) }} yil.</p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-feather"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">{{ __('field.date_signed') }}</h6>
                        <p>
                            {{ date('d.m.Y H:i', strtotime($document->created_at)) }}
                        </p>
                    </div>
                </a>
            </div>
            <div class="feed-item">
                <a href="#">
                    <div class="feeds-left"><i class="ik ik-user"></i></div>
                    <div class="feeds-body">
                        <h6 class="title text-blue">{{ __('field.performer') }}</h6>
                        <p>
                            {{ $document->secretary->personal?->l_name??'-' }}
                            {{ $document->secretary->personal?->f_name??'-' }}
                        </p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
