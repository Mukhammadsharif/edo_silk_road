@extends('layouts.app')
@section('content')
    <link href="{{ asset('/departments/tree.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-home bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.departments') }}</h5>
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
                                <a href="{{ route('departments.index', ['id' => $organization_id]) }}">{{ __('menu.departments') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <h3><i class="ik ik-plus-circle"></i> {{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                @php $lang_key = session()->get('locale')??'uz'; @endphp
                                <ul><h4>{{ __('menu.departments') }}</h4></ul>
                                <section class="list-tree">
                                    <ul>
                                        @foreach($parentDepartments as $key => $item)
                                            <li>
                                                {{ $item['name_'.$lang_key] }}
                                                @if(count($item->childs))
                                                    @include('admin.departments.manageChild',['childs' => $item->childs])
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('departments.store') }}" class="forms-sample">
                                    @csrf
                                    <input name="organization_id" value="{{ $organization_id }}" hidden>

                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label>{{ __('field.parent_id') }}</label>
                                            <select name="parent_id" class="form-control select2">
                                                <option value="">{{ __('action.choose') }}</option>
                                                @foreach($departments as $key => $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $key + 1 }}. {{ $item['name_'.$lang_key] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-12">
                                            <br>
                                            <label>{{ __('field.name_uz') }}</label>
                                            <input type="text" name="name_uz" class="form-control{{($errors->first('name_uz') ? " form-control-warning" : "")}}" value="{{ old('name_uz') }}">
                                            {!! $errors->first('name_uz', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="col-sm-12">
                                            <br>
                                            <label>{{ __('field.name_kr') }}</label>
                                            <input type="text" name="name_kr" class="form-control" value="{{ old('name_kr') }}">
                                        </div>
                                        <div class="col-sm-12">
                                            <br>
                                            <label>{{ __('field.name_ru') }}</label>
                                            <input type="text" name="name_ru" class="form-control" value="{{ old('name_ru') }}">
                                        </div>
                                        <div class="col-sm-12">
                                            <br>
                                            <label>{{ __('field.name_en') }}</label>
                                            <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('departments.index', ['id' => $organization_id]) }}" class="btn btn-light">
                                                <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ik ik-save"></i> {{ __('action.save') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.select2').select2()
    })
</script>
