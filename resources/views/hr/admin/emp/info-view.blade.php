@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.emp') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-3">
                    @include('hr.admin.emp.breadcrumb')
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                @include('hr.admin.emp.left-menu')
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-block bg-aqua">
                        <h3><i class="ik ik-user-check"></i> {{ __('menu.emp_info') }}</h3>
                    </div>
                    <div class="card-body template-demo">
                        <div class="col-md-12">
                            <form method="POST" action="{{ route('hr.emp.info-update', [$user->id, $user->user_hash]) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
{{--                                    <div class="col-sm-4">--}}
{{--                                        <div class="form-group">--}}
{{--                                            <label>{{ __('field.l_name') }} <span class="text-red">*</span></label>--}}
{{--                                            <input type="text" name="l_name" class="form-control{{($errors->first('l_name') ? " form-control-warning" : "")}}" value="{{ $personal->l_name }}">--}}
{{--                                            {!! $errors->first('l_name', '<p class="text-danger">:message</p>') !!}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-sm-4">--}}
{{--                                        <div class="form-group">--}}
{{--                                            <label>{{ __('field.f_name') }} <span class="text-red">*</span></label>--}}
{{--                                            <input type="text" name="f_name" class="form-control{{($errors->first('f_name') ? " form-control-warning" : "")}}" value="{{ $personal->f_name }}">--}}
{{--                                            {!! $errors->first('f_name', '<p class="text-danger">:message</p>') !!}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-sm-4">--}}
{{--                                        <div class="form-group">--}}
{{--                                            <label>{{ __('field.m_name') }} <span class="text-red">*</span></label>--}}
{{--                                            <input type="text" name="m_name" class="form-control" value="{{ $personal->m_name }}">--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ __('field.l_name') }} (UZ Latin) <span class="text-red">*</span></label>
                                            <input type="text" name="l_name_uz" class="form-control{{($errors->first('l_name_uz') ? ' form-control-warning' : '')}}" value="{{ $personal->l_name_uz ?? $personal->l_name }}" oninput="transliterate(this, 'l_name')">
                                            {!! $errors->first('l_name_uz', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.l_name') }} (UZ Cyrillic)</label>
                                            <input type="text" name="l_name_kr" class="form-control" value="{{ $personal->langName->l_name_kr ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.l_name') }} (RU)</label>
                                            <input type="text" name="l_name_ru" class="form-control" value="{{ $personal->langName->l_name_ru ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.l_name') }} (EN)</label>
                                            <input type="text" name="l_name_en" class="form-control" value="{{ $personal->langName->l_name_en ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ __('field.f_name') }} (UZ Latin) <span class="text-red">*</span></label>
                                            <input type="text" name="f_name_uz" class="form-control{{($errors->first('f_name_uz') ? ' form-control-warning' : '')}}" value="{{ $personal->f_name_uz ?? $personal->f_name }}" oninput="transliterate(this, 'f_name')">
                                            {!! $errors->first('f_name_uz', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.f_name') }} (UZ Cyrillic)</label>
                                            <input type="text" name="f_name_kr" class="form-control" value="{{ $personal->langName->f_name_kr ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.f_name') }} (RU)</label>
                                            <input type="text" name="f_name_ru" class="form-control" value="{{ $personal->langName->f_name_ru ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.f_name') }} (EN)</label>
                                            <input type="text" name="f_name_en" class="form-control" value="{{ $personal->langName->f_name_en ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ __('field.m_name') }} (UZ Latin) <span class="text-red">*</span></label>
                                            <input type="text" name="m_name_uz" class="form-control" value="{{ $personal->m_name_uz ?? $personal->m_name }}" oninput="transliterate(this, 'm_name')">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.m_name') }} (UZ Cyrillic)</label>
                                            <input type="text" name="m_name_kr" class="form-control" value="{{ $personal->langName->m_name_kr ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.m_name') }} (RU)</label>
                                            <input type="text" name="m_name_ru" class="form-control" value="{{ $personal->langName->m_name_ru ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('field.m_name') }} (EN)</label>
                                            <input type="text" name="m_name_en" class="form-control" value="{{ $personal->langName->m_name_en ?? '' }}">
                                        </div>
                                    </div>


                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ __('field.doc_pin') }}</label>
                                            <input type="text" name="doc_pin" id="p_pin" class="form-control" value="{{ $personal->doc_pin }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label>{{ __('field.doc_number') }}</label>
                                            <input type="text" name="doc_number" id="p_num" class="form-control" value="{{ $personal->doc_number }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label>{{ __('field.doc_type') }}</label>
                                            @php($doc_type = $user->userPersonal->doc_type??0)
                                            <select class="form-control" name="doc_type">
                                                <option value="1" @if($doc_type == 1) selected @endif>Passport</option>
                                                <option value="2" @if($doc_type == 2) selected @endif>ID Card</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ __('field.birthday') }}</label>
                                            <input type="date" name="birthday" class="form-control" value="{{ date('Y-m-d', strtotime($personal->birthday??now())) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ __('field.gender') }}</label>
                                            @php($gender = $user->userPersonal->gender??0)
                                            <select class="form-control" name="gender">
                                                <option value="1" @if($gender == 1) selected @endif>Erkak</option>
                                                <option value="2" @if($gender == 2) selected @endif>Ayol</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>{{ __('field.phone') }}</label>
                                            <input type="text" name="phone" id="tel_number" class="form-control" value="{{ $personal->phone }}">
                                        </div>
                                    </div>
                                </div>
                                @if($user->status == 1)
                                <button type="submit" class="btn btn-primary">
                                    <i data-lucide="save"></i> {{ __('action.save') }}
                                </button>
                                @endif
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $('#p_num').inputmask({mask: 'AA9999999'});
        $('#p_pin').inputmask({mask: '99999999999999'});
        $('#tel_number').inputmask({mask: '999999999'});
        $('#tel_number2').inputmask({mask: '999'});


        function transliterate(element, field) {
            const value = element.value;

            const toTitleCase = (text) => {
                return text
                    .split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            };

            const toUzbekCyrillic = (text) => {
                const map = {
                    'a': 'а', 'b': 'б', 'd': 'д', 'e': 'е', 'f': 'ф', 'g': 'г', 'h': 'ҳ',
                    'i': 'и', 'j': 'ж', 'k': 'к', 'l': 'л', 'm': 'м', 'n': 'н', 'o': 'о',
                    'p': 'п', 'q': 'қ', 'r': 'р', 's': 'с', 't': 'т', 'u': 'у', 'v': 'в',
                    'x': 'х', 'y': 'й', 'z': 'з', 'o\'': 'ў', 'g\'': 'ғ', 'sh': 'ш', 'ch': 'ч',
                    'ng': 'нг'
                };
                let result = text.toLowerCase();
                result = result.replace(/o\'/g, 'ў').replace(/g\'/g, 'ғ').replace(/sh/g, 'ш').replace(/ch/g, 'ч').replace(/ng/g, 'нг');
                result = result.split('').map(char => map[char] || char).join('');
                return toTitleCase(result);
            };

            const toRussian = (text) => {
                const map = {
                    'a': 'а', 'b': 'б', 'd': 'д', 'e': 'е', 'f': 'ф', 'g': 'г', 'h': 'х',
                    'i': 'и', 'j': 'ж', 'k': 'к', 'l': 'л', 'm': 'м', 'n': 'н', 'o': 'о',
                    'p': 'п', 'q': 'к', 'r': 'р', 's': 'с', 't': 'т', 'u': 'у', 'v': 'в',
                    'x': 'х', 'y': 'й', 'z': 'з', 'o\'': 'у', 'g\'': 'г', 'sh': 'ш', 'ch': 'ч',
                    'ng': 'нг'
                };
                let result = text.toLowerCase();
                result = result.replace(/o\'/g, 'у').replace(/g\'/g, 'г').replace(/sh/g, 'ш').replace(/ch/g, 'ч').replace(/ng/g, 'нг');
                result = result.split('').map(char => map[char] || char).join('');
                return toTitleCase(result);
            };

            const toEnglish = (text) => {
                const map = {
                    'a': 'a', 'b': 'b', 'd': 'd', 'e': 'e', 'f': 'f', 'g': 'g', 'h': 'h',
                    'i': 'i', 'j': 'j', 'k': 'k', 'l': 'l', 'm': 'm', 'n': 'n', 'o': 'o',
                    'p': 'p', 'q': 'q', 'r': 'r', 's': 's', 't': 't', 'u': 'u', 'v': 'v',
                    'x': 'x', 'y': 'y', 'z': 'z', 'o\'': 'o', 'g\'': 'g', 'sh': 'sh', 'ch': 'ch',
                    'ng': 'ng'
                };
                let result = text.toLowerCase();
                result = result.replace(/o\'/g, 'o').replace(/g\'/g, 'g').replace(/sh/g, 'sh').replace(/ch/g, 'ch').replace(/ng/g, 'ng');
                result = result.split('').map(char => map[char] || char).join('');
                return toTitleCase(result);
            };

            document.querySelector(`input[name=${field}_kr]`).value = toUzbekCyrillic(value);
            document.querySelector(`input[name=${field}_ru]`).value = toRussian(value);
            document.querySelector(`input[name=${field}_en]`).value = toEnglish(value);
        }
    </script>
@endsection
