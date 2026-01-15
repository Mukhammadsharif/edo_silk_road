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
                                <a href="{{ route('organizations.index') }}">{{ __('menu.organizations') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-block">
                        <h4><i class="ik ik-edit"></i> {{ __('action.edit') }}</h4>
                    </div>
                    <div class="card-body template-demo">
                        <div class="row">
                            <div class="col-md-6">
                                @php $lang_key = session()->get('locale')??'uz'; @endphp
                                <ul><h4>{{ __('menu.organizations') }}</h4></ul>
                                <section class="list-tree">
                                    <ul>
                                        @foreach($parentOrganizations as $key => $item)
                                            <li>
                                                {{ $item['name_'.$lang_key]??$item['name_uz'] }}
                                                @if(count($item->childs))
                                                    @include('admin.organizations.manageChild',['childs' => $item->childs])
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <form method="POST" action="{{ route('organizations.update', $model->id) }}" class="forms-sample">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-group">
                                            <label>{{ __('field.parent_id') }}</label>
                                            <select name="parent_id" class="form-control select2">
                                                <option value="0">{{ __('field.not_selected') }}</option>
                                                @foreach($organizations as $key => $item)
                                                    <option value="{{ $item->id }}" {{ ($item->id == $model->parent_id ? "selected":"") }}>
                                                        {{ $key + 1 }}. {{ $item['name_'.$lang_key]??$item['name_uz'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.name_uz') }}</label>
                                            <input type="text" name="name_uz" class="form-control" placeholder="{{ __('field.name_uz') }}" value="{{ $model->name_uz }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.name_kr') }}</label>
                                            <input type="text" name="name_kr" class="form-control" placeholder="{{ __('field.name_kr') }}" value="{{ $model->name_kr }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.name_ru') }}</label>
                                            <input type="text" name="name_ru" class="form-control" placeholder="{{ __('field.name_ru') }}" value="{{ $model->name_ru }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.name_en') }}</label>
                                            <input type="text" name="name_en" class="form-control" placeholder="{{ __('field.name_en') }}" value="{{ $model->name_en }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.tax_number') }}</label>
                                            <input type="number" name="tax_number" class="form-control" placeholder="{{ __('field.tax_number') }}" value="{{ $model->tax_number??'' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.parent_id') }}</label>
                                            <select name="parent_id" class="form-control select2">
                                                <option value="0">{{ __('field.not_selected') }}</option>
                                                @foreach($users as $key => $user)
                                                    <option value="{{ $user->id }}" {{ ($user->id == $model->owner_user_id ? "selected":"") }}>
                                                        {{ $key + 1 }}. {{ $user->userPersonal->fullName()??'' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-radio">
                                            <label>{{ __('field.status') }}</label>
                                            <div class="radio radio-inline radio-success">
                                                <label>
                                                    <input type="radio" name="status" value="1" {{ ($model->status == 1) ? 'checked':'' }}>
                                                    <i class="helper"></i>{{ __('status.active') }}
                                                </label>
                                            </div>
                                            <div class="radio radio-inline radio-danger">
                                                <label>
                                                    <input type="radio" name="status" value="2" {{ ($model->status == 2) ? 'checked':'' }}>
                                                    <i class="helper"></i>{{ __('status.delete') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('organizations.index') }}" class="btn btn-light">
                                                    <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i data-lucide="save"></i> {{ __('action.save') }}
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
        </div>
    </div>
@endsection

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.select2').select2()
    })
</script>
