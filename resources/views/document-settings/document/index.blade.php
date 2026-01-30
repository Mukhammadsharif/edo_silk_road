@extends('layouts.app')

@section('content')
    <link href="{{ asset('/departments/tree.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.doc_type_name') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('title.doc_type') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                @php $lang_key = session()->get('locale'); @endphp
                <ul><h4>{{ __('menu.doc_types') }}</h4></ul>
                <section class="list-tree">
                    <ul>
                        @foreach($parentDocumentTypes as $key => $item)
                            <li>
                                <b class="text-primary">{{ $item->order_number }}. {{ $item->lang($item->lang_id) }}</b> - ({{ $item->doc_code }})
                                <a href="{{ route('document-type.edit', $item->id) }}">
                                    <i class="ik ik-edit-1 f-16 ml-5 text-info"></i>
                                </a>
                                @if(count($item->childs))
                                    @include('document-settings.document.manageChild',['childs' => $item->childs])
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('document-type.create') }}" class="btn btn-primary">
                            <i data-lucide="circle-plus"></i> {{ __('action.create') }}
                        </a>
                    </div>
                    <div class="card-header d-block">
                        <form action="{{ route('document-type.search') }}" method="post" role="search"
                              class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-lg-4 margin-top-5">
                                    <input type="text" name="doc_name" placeholder="{{ __('input.doc_name') }}"
                                           value="{{ $doc_name??'' }}" class="form-control">
                                </div>
                                    <button type="submit" class="btn btn-info mr-5 mb-2 mr-sm-2 mb-2">
                                        <i data-lucide="search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                    </button>
                                    <a href="{{ route('document-type.index') }}" class="btn btn-light">
                                        <i data-lucide="refresh-ccw"></i> {{ __('action.clear') }}
                                    </a>
                            </div>
                        </form><hr>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.parent_id') }}</th>
                                    <th>{{ __('field.name') }}</th>
                                    <th>{{ __('field.order_number') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($k = 1)
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>{{ $k++ }}</td>
                                        <td class="text-muted">
                                            @php($parent = $model->parent->title??0)
                                            @if($parent)
                                                {{ $parent }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $model->lang($model->lang_id) }}
                                        </td>
                                        <td>{{ $model->order_number }}</td>
                                        <td>
                                            <span
                                                class="badge badge-pill @if($model->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $model->isActive($model->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('document-type.edit', $model->id) }}"
                                               class="btn btn-sm btn-info">
                                                <i data-lucide="square-pen"></i> {{ __('action.edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="5">{{ __('field.not_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            {!! $models->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
