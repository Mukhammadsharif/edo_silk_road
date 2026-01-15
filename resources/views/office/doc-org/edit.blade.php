@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-home bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('field.doc_org_name') }}</h5>
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
                                <a href="{{ route('doc-org.index') }}">{{ __('field.doc_org_name') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit"></i> {{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('doc-org.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="title">{{ __('field.name_uz') }}</label>
                                <input id="name_uz" type="text" name="name_uz" class="form-control" placeholder="{{ __('field.title') }}"
                                       value="{{ $model->name_uz }}">
                            </div>
                            <div class="form-group">
                                <label for="name_ru">{{ __('field.name_ru') }}</label>
                                <input id="title" type="text" name="name_ru" class="form-control" placeholder="{{ __('field.title') }}"
                                       value="{{ $model->name_ru }}">
                            </div>
                            <div class="form-group">
                                <label for="name_en">{{ __('field.name_en') }}</label>
                                <input id="title" type="text" name="name_en" class="form-control" placeholder="{{ __('field.title') }}"
                                       value="{{ $model->name_en }}">
                            </div>

                            <div class="form-group">
                                <label for="for_doc_type">{{ __('field.journal_type') }}</label>
                                <select id="for_doc_type" class="form-control select2" style="width: 100%" name="for_doc_type" required>
                                    <option value=""> {{ __('field.empty') }} </option>
                                    @foreach($journals as $key => $journal)
                                        <option value="{{ $journal->id }}" {{ $model->for_doc_type == $journal->id ? 'selected' : '' }}>
                                            {{ $key+1 }}. {{ $journal->lang($journal->lang_id) }}
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
                            <a href="{{ route('doc-org.index') }}" class="btn btn-light">
                                <i class="ik ik-x"></i>{{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i>{{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        setTimeout( function() {
            $('.hideAlert').hide();
        }, 5000);
    </script>
@endsection
