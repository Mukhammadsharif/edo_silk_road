@extends('layouts.app')
@section('content')
    <link href="{{ asset('/departments/tree.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-folder-minus bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.organizations') }}</h5>
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
                                <a href="#">{{ __('menu.organizations') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <a href="{{ route('organizations.create') }}" class="btn btn-primary">
                            <i class="ik ik-plus-circle"></i>{{ __('action.create') }}
                        </a>
                    </div>
                    <div class="card-body template-demo">
                        <div class="row">
                            <div class="col-md-4">
                                @php $lang_key = session()->get('locale')??'uz'; @endphp
                                <ul><h4>{{ __('menu.organizations') }}</h4></ul>
                                <section class="list-tree">
                                    <ul>
                                        @foreach($parentOrganizations as $key => $item)
                                            <li>
                                                {{ $item['name_'.$lang_key]??$item['name_uz'] }}
                                                @if($item->childs && count($item->childs))
                                                    @include('admin.organizations.manageChild',['childs' => $item->childs])
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            </div>

                            <div class="col-md-8">
                                <div class="dt-responsive">
                                    <form action="{{ route('organizations.index') }}" method="post" role="search" class="form-horizontal">
                                        @csrf
                                        <div class="row">
                                            <div class="col-4">
                                                <input type="text" name="s_text" value="{{ $s_text??'' }}" class="form-control" placeholder="{{ __('action.search') }}">
                                            </div>
                                            <button type="submit" class="btn btn-info mr-1">
                                                <i class="ik ik-search"></i> {{ __('action.search') }} ({{ $organizations->total() }})
                                            </button>
                                            <a href="{{ route('organizations.index') }}" class="btn btn-light">
                                                <i class="ik ik-refresh-ccw"></i>  {{ __('field.clear') }}
                                            </a>
                                        </div>
                                    </form>
                                    <table class="table table-striped table-bordered text-sm-left">
                                        <thead>
                                        <tr>
                                            <th>{{ __('field.number') }}</th>
                                            <th>{{ __('field.name') }}</th>
                                            <th>{{ __('field.parent_id') }}</th>
                                            <th>{{ __('field.status') }}</th>
                                            <th>{{ __('field.action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($organizations as $key => $item)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td class="w-40">
                                                    <a style="text-decoration-line: underline; color: deepskyblue" href="{{ route('departments.index', ['id' => $item->id]) }}">
                                                        {{ $item->lang($item->id) }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge badge-pill badge-secondary mb-1">
                                                        {{ $item->parentlang($item->parent_id)??'-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-pill @if($item->status == 1) badge-green @else badge-danger @endif text-white">
                                                        {{ $item->isActive($item->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('organizations.edit', $item->id) }}" class="btn btn-info">
                                                        <i class="ik ik-edit-1"></i> {{ __('action.edit') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="8">{{ __('field.not_found') }}</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                    {!! $organizations->links() !!}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{--    <script src="{{ asset('/departments/treeview.js') }}"></script>--}}
@endsection
