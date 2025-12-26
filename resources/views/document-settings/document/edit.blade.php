@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.doc_types') }}</h5>

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
                                <a href="{{ route('document-type.index') }}">{{ __('menu.doc_types') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit"></i> {{ __('action.edit') }}</h3>
                    </div>
                    <form method="POST" action="{{ route('document-type.update', $model->id) }}" class="forms-sample">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                                <div class="form-group">
                                    <label>{{ __('field.name_uz') }}</label>
                                    <input type="text" name="title" class="form-control" placeholder="{{ __('field.title') }}" value="{{ $model->title }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="parent_id">{{ __('field.parent_id') }}</label>
                                    <select id="parent_id" class="form-control select2" name="parent_id" style="width: 100%">
                                        <option value="0"> {{ __('field.empty') }} </option>
                                        @foreach($journalTypes as $key => $value)
                                            <option value="{{ $value->id }}" {{ $value->id === $model->parent_id ? 'selected' : '' }}>
                                                {{ $key+1 }}. {{ $value->lang($value->lang_id) }} ({{ $value->title }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="lag_id">{{ __('field.doc_lang') }}</label>
                                    <select id="lag_id" class="form-control select2" style="width: 100%" name="lang_id" required>
                                        <option value=""> {{ __('field.empty') }} </option>
                                        @foreach($languages as $key => $value)
                                            <option value="{{ $value->id }}" {{ $value->id === $model->lang_id ? 'selected' : '' }}>
                                                {{ $key+1 }}. {{ __($value->group.'.'.$value->key) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="doc_code">{{ __('field.code') }}</label>
                                    <input id="doc_code" type="text" name="doc_code" class="form-control" value="{{ $model->doc_code }}" required>
                                </div>

                                <div class="form-group clearfix">
                                    <div class="d-inline mr-3">
                                        <label>{{ __('field.status') }}</label>
                                    </div>
                                    <div class="icheck-success d-inline mr-3">
                                        <input type="radio" id="edit_status1" name="status" value="active" {{ $model->status ? 'checked' : '' }}>
                                        <label class="text-success" for="edit_status1">{{ __('field.active') }}</label>
                                    </div>
                                    <div class="icheck-warning d-inline mr-3">
                                        <input type="radio" id="edit_status2" name="status" value="inactive" {{ !$model->status ? 'checked' : '' }}>
                                        <label class="text-warning" for="edit_status2">{{ __('field.delete') }}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('field.order_number') }}</label>
                                    <input type="number" name="order_number" class="form-control" value="{{ $model->order_number??0 }}" required>
                                </div>
                        </div>

                        <div class="card-footer d-flex flex-row justify-content-end">
                            <a href="{{ route('document-type.index') }}" class="btn btn-danger mr-5">
                                <i class="ik ik-x"></i>{{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ik ik-save"></i>{{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <form method="POST" action="{{ route('document.step.store') }}" class="forms-sample">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="ik ik-edit"></i> {{ __('field.order') }}</h3>
                        </div>

                        <div class="card-body">
                            <input name="journal_type_id" value="{{ $model->id }}" hidden>
                            <ul class="list-group" style="margin: 0; padding: 0">
                                @foreach($execution_roles as $key => $execution_role) {{ $key + 1 }}.
                                    <li class="list-group-item {{ $listGroupItemColors[$key] }}" style="display: flex; flex-direction: row; align-items: center">
                                        @if($execution_role->doc_code && trim($execution_role->doc_code) === 'RESOL')
                                            <input class="form-control w-10" type="checkbox"
                                                   id="{{ $execution_role->id }}"
                                                   checked disabled>
                                            <input type="hidden"
                                                   name="{{ $execution_role->doc_code }}"
                                                   value="{{ $execution_role->id }}">
                                        @else
                                            <input class="form-control w-10" type="checkbox"
                                                   id="{{ $execution_role->id }}"
                                                   value="{{ $execution_role->id }}"
                                                   name="{{ $execution_role->doc_code }}"
                                                {{ in_array($execution_role->id, $journal_exec_steps->pluck('exec_role_id')->toArray()) ? 'checked' : '' }}
                                            >
                                        @endif
                                        <label for="{{ $execution_role->id }}">{{ $execution_role->title }} - <i>{{ $listGroupDescriptions[$key]??'' }}</i></label>
                                    </li>
                                @endforeach
                            </ul>

                            <hr>

                            <input name="journal_type_id" value="{{ $model->id }}" hidden>

                            <div class="col-sm-12 mb-30">
                                <p>
                                    <input id="control" type="checkbox" name="control" class="js-switch"
                                           @if($model->controlStatus && $model->controlStatus->status == 1) checked @endif
                                    />
                                    <label for="control">{{ __('control.control') }}
                                        - <i>Rahbar rezolyutsiyasi uchun fishka (ijrochilarni biriktirish, muddat belgilash va ijro turini tanlash) kirituvchi xodim</i>
                                    </label>
                                </p>
                            </div>
                        </div>

                        <div class="card-footer d-flex flex-row justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ik ik-save"></i>{{ __('action.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($journal_exec_steps->count())
                @foreach($journal_exec_steps as $journal_exec_step)
                    <div class="col-md-3">
                        <form method="POST" action="{{ route('document.step.roles', ['step_id' => $journal_exec_step->id, 'j_type_id' => $model->id]) }}">
                            @csrf
                            @method('POST')
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="ik ik-plus-circle"></i> {{ $journal_exec_step->executionRole->lang($journal_exec_step->executionRole->lang_id) }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-30">
                                            @foreach($roles as $key => $item)
                                                <p>{{ $key+1 }}.
                                                    <input type="checkbox" name="role_ids[]" value="{{ $item->id }}" class="js-switch"
                                                           @if(in_array($item->id, $journal_exec_step->executionStepRolesIds->pluck('role_id')->toArray())) checked @endif
                                                    />
                                                    <label>{{ $item->lang($item->id) }}</label>
                                                </p>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer d-flex flex-row justify-content-end">
                                    <div class="form-group" hidden>
                                        <label>{{ __('field.status') }}</label>
                                        <input type="number" name="status" class="form-control" value="{{ $journal_exec_step->exec_status??0 }}" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="ik ik-save"></i> {{ __('action.save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

<script src="{{ asset('/assets/js/js-sortable.js') }}"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        let sortableElement = document.getElementById('sortable');

        if (sortableElement) {
            new Sortable(sortableElement, {
                animation: 150, // Add some animation when sorting
                ghostClass: 'sortable-ghost', // Class for the placeholder during drag
            });
        } else {
            console.error("Element #sortable not found");
        }
    });
</script>
