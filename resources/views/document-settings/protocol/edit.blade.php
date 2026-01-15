@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.prt_doc_types') }}</h5>
                            <span>{{ __('menu.list') }}</span>
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
                                <a href="{{ route('protocol-types.index') }}">{{ __('menu.prt_doc_types') }}</a>
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
                    <div class="card-header">
                        <h3>{{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('protocol-types.update', $model->id) }}"
                              class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-8" hidden>
                                            <div class="form-group">
                                                <label>{{ __('field.parent_id') }}</label>
                                                <select name="parent_id" class="form-control select2">
                                                    <option value="0">{{ __('action.choose') }}</option>
                                                    @foreach($docTypes as $key => $item)
                                                        <option
                                                            value="{{ $item->id }}" {{ ($item->id == $model->parent_id ? "selected":"") }}>
                                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('field.title') }} <span
                                                        class="text-red">*</span></label>
                                                <input type="text" class="form-control" name="title"
                                                       value="{{ $model->title }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('field.lang_id') }}</label>
                                                <select class="form-control select2" name="lang_id">
                                                    <option value="">{{ __('field.not_selected') }}</option>
                                                    @foreach($languages as $key => $value)
                                                        <option
                                                            value="{{ $value->id }}" {{ ($value->id == $model->lang_id ? "selected":"") }}>
                                                            {{ $key+1 }}. {{ __($value->group.'.'.$value->key) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('field.order_number') }}</label>
                                                <input type="number" class="form-control" name="order_number"
                                                       value="{{ $model->order_number }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('field.doc_code') }}</label>
                                                <input type="text" class="form-control" name="doc_code"
                                                       value="{{ $model->doc_code }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-radio">
                                                <label>{{ __('field.status') }}</label>
                                                <div class="radio radio-inline radio-success">
                                                    <label>
                                                        <input type="radio" name="status"
                                                               value="1" {{ ($model->status == 1) ? 'checked':'' }}>
                                                        <i class="helper"></i>{{ __('status.active') }}
                                                    </label>
                                                </div>
                                                <div class="radio radio-inline radio-danger">
                                                    <label>
                                                        <input type="radio" name="status"
                                                               value="2" {{ ($model->status == 2) ? 'checked':'' }}>
                                                        <i class="helper"></i>{{ __('status.delete') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>{{ __('field.select_members') }} <span class="text-red">*</span></label>
                                            <div class="input-group mb-2 mt-2">
                                                <input type="text" class="form-control" id="userSearch" onkeyup="searchUserFunction()"
                                                       placeholder="{{ __('action.search') }}...">
                                            </div>
                                            <ul id="sortable" class="sortable" style="max-height: 400px; overflow-x: auto">
                                                @foreach($users as $key => $item)
                                                    <li class="form-group select-user"
                                                        data-id="{{ $item->id }}"
                                                        style="display: flex; flex-direction: row; align-items: center; justify-content: space-between">
                                                        <div style="display: flex; flex-direction: row; align-items: center; width: 75%">
                                                            <input class="form-control w-10"
                                                                   type="checkbox"
                                                                   id="{{ $item->id }}"
                                                                   value="{{ $item->id }}"
                                                                   name="user_id[]"
                                                                   @if(array_key_exists($item->id, $selectedUsers)) checked @endif>
                                                            <div class="p0 text-primary">
                                                                {{ $item->personal->l_name ?? '' }} {{ $item->personal->f_name ?? '' }}
                                                                <p class="text-muted">
                                                                    @php($department = $item->department ?? 0)
                                                                    @if($department)
                                                                        {{ $department->lang($department->id) }}
                                                                    @endif
                                                                    -
                                                                    @php($position = $item->position ?? 0)
                                                                    @if($position)
                                                                        {{ $position->lang($position->id) }}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex">
                                                            <select name="user_role_id[{{ $item->id }}]" class="form-control mr-5" style="width: 160px">
                                                                @foreach($user_roles as $role)
                                                                    <option value="{{ $role->code }}"
                                                                            @if(
                                                                                (isset($selectedUsers[$item->id]) && $selectedUsers[$item->id] == $role->code) ||
                                                                                (!isset($selectedUsers[$item->id]) && $role->code == 2)
                                                                            ) selected @endif>
                                                                        {{ $role->lang($role->id) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <a href="{{ route('protocol-types.index') }}" class="btn btn-light">
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

            <div class="col-md-12">
                <form method="POST" action="{{ route('prt.doc.sign.roles') }}" class="forms-sample">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="ik ik-edit"></i> {{ __('field.sign_roles') }}</h3>
                        </div>

                        <div class="card-body">
                            <input name="protocol_type_id" value="{{ $model->id }}" hidden>
                            <ul id="signRolesSortable" class="list-group" style="margin: 0; padding: 0">
                                @foreach($signRoleTypes as $key => $signRoleType)
                                    <li class="list-group-item {{ $listGroupItemColors[$key % count($listGroupItemColors)] }}"
                                        style="display: flex; flex-direction: row; align-items: center"
                                        data-id="{{ $signRoleType->code }}">
                                        @if($signRoleType->code == 1)
                                            <input class="form-control w-10" type="checkbox"
                                                   id="role_{{ $signRoleType->code }}"
                                                   checked disabled>
                                            <input type="hidden" name="roles[]" value="{{ $signRoleType->code }}">
                                        @else
                                            <input class="form-control w-10" type="checkbox"
                                                   id="role_{{ $signRoleType->code }}"
                                                   value="{{ $signRoleType->code }}"
                                                   name="roles[]"
                                                {{ in_array($signRoleType->code, $savedCodes) ? 'checked' : '' }}>
                                        @endif
                                        <label class="label text-bold"
                                               for="role_{{ $signRoleType->code }}">{{ $signRoleType->lang($signRoleType->id) ?? '' }}</label>
                                    </li>
                                @endforeach
                            </ul>

                            <br><br>
                            <h4 class="sub-title">Imzolash turi</h4>

                            <div class="form-radio">
                                <div class="radio radio-inline">
                                    <label>
                                        <input type="radio" name="sign_type" value="0"
                                            {{ $model->sign_type == 0 ? 'checked' : '' }}>
                                        <i class="helper"></i>Ketma-ket
                                    </label>
                                </div>
                                <div class="radio radio-inline">
                                    <label>
                                        <input type="radio" name="sign_type" value="1"
                                            {{ $model->sign_type == 1 ? 'checked' : '' }}>
                                        <i class="helper"></i>Parallel
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex flex-row justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i>{{ __('action.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('/assets/js/js-sortable.js') }}"></script>

    <script>
        // Debug: Log to confirm script execution
        console.log('Script loaded: Initializing search and sortable functions');

        const sortableElement = document.getElementById('sortable');
        const sortableSecretaryElement = document.getElementById('signRolesSortable');

        function searchUserFunction() {
            const input = document.getElementById('userSearch').value.toLowerCase();
            const nodes = document.querySelectorAll('#sortable .select-user');
            console.log('searchUserFunction called with input:', input, 'Nodes found:', nodes.length);

            nodes.forEach(node => {
                const textContent = node.querySelector('.text-primary').textContent.toLowerCase();
                node.style.display = textContent.includes(input) ? 'flex' : 'none';
            });

            // Refresh sortable after filtering
            if (sortableElement) {
                Sortable.get(sortableElement).sort(Sortable.get(sortableElement).toArray());
            }

            // Reapply disabled states after filtering
            reapplyDisabledStates();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Sortable.js for both lists

            if (sortableElement) {
                new Sortable(sortableElement, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                });
                console.log('Sortable initialized for #sortable');
            } else {
                console.error('Element #sortable not found');
            }

            if (sortableSecretaryElement) {
                new Sortable(sortableSecretaryElement, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    handle: '.list-group-item',
                    filter: 'li:first-child', // первый элемент
                    onMove: function (evt) {
                        return evt.related !== evt.from.firstElementChild; // запрет на перемещение
                    }
                });
                console.log('Sortable initialized for #signRolesSortable');
            } else {
                console.error('Element #signRolesSortable not found');
            }
        });
    </script>
@endsection
