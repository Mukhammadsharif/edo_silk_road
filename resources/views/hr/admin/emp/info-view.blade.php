@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="user-check"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('menu.emp_info') }}</p>
                    </div>
                </div>
                <div class="header-right">
                    @include('hr.admin.emp.breadcrumb')
                </div>
            </div>
        </div>

        <div class="col-md-12">
            @include('templates.alert')
        </div>

        <div class="hr-page-layout">
            @include('hr.admin.emp.left-menu')

            <div class="hr-content-card">
                <div class="hr-card-header">
                    <h3><i data-lucide="user-check"></i> {{ __('menu.emp_info') }}</h3>
                </div>
                <div class="hr-card-body">
                    <form method="POST" action="{{ route('hr.emp.info-update', [$user->id, $user->user_hash]) }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <!-- Language Name Inputs - Last Name -->
                        <div class="lang-inputs-group">
                            <div class="lang-input-col">
                                <div class="lang-label-header">Uzbek Latin</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.l_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="l_name_uz" class="form-control-modern" value="{{ $personal->l_name_uz ?? $personal->l_name }}" oninput="transliterate(this, 'l_name')" required>
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">Uzbek Cyrillic</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.l_name') }}</label>
                                    <input type="text" name="l_name_kr" class="form-control-modern" value="{{ $personal->langName->l_name_kr ?? '' }}">
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">Russian</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.l_name') }}</label>
                                    <input type="text" name="l_name_ru" class="form-control-modern" value="{{ $personal->langName->l_name_ru ?? '' }}">
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">English</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.l_name') }}</label>
                                    <input type="text" name="l_name_en" class="form-control-modern" value="{{ $personal->langName->l_name_en ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Language Name Inputs - First Name -->
                        <div class="lang-inputs-group">
                            <div class="lang-input-col">
                                <div class="lang-label-header">Uzbek Latin</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.f_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="f_name_uz" class="form-control-modern" value="{{ $personal->f_name_uz ?? $personal->f_name }}" oninput="transliterate(this, 'f_name')" required>
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">Uzbek Cyrillic</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.f_name') }}</label>
                                    <input type="text" name="f_name_kr" class="form-control-modern" value="{{ $personal->langName->f_name_kr ?? '' }}">
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">Russian</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.f_name') }}</label>
                                    <input type="text" name="f_name_ru" class="form-control-modern" value="{{ $personal->langName->f_name_ru ?? '' }}">
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">English</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.f_name') }}</label>
                                    <input type="text" name="f_name_en" class="form-control-modern" value="{{ $personal->langName->f_name_en ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Language Name Inputs - Middle Name -->
                        <div class="lang-inputs-group">
                            <div class="lang-input-col">
                                <div class="lang-label-header">Uzbek Latin</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.m_name') }}</label>
                                    <input type="text" name="m_name_uz" class="form-control-modern" value="{{ $personal->m_name_uz ?? $personal->m_name }}" oninput="transliterate(this, 'm_name')">
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">Uzbek Cyrillic</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.m_name') }}</label>
                                    <input type="text" name="m_name_kr" class="form-control-modern" value="{{ $personal->langName->m_name_kr ?? '' }}">
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">Russian</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.m_name') }}</label>
                                    <input type="text" name="m_name_ru" class="form-control-modern" value="{{ $personal->langName->m_name_ru ?? '' }}">
                                </div>
                            </div>
                            <div class="lang-input-col">
                                <div class="lang-label-header">English</div>
                                <div class="form-group-modern">
                                    <label>{{ __('field.m_name') }}</label>
                                    <input type="text" name="m_name_en" class="form-control-modern" value="{{ $personal->langName->m_name_en ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Fields -->
                        <div class="form-grid three-columns">
                            <div class="form-group-modern">
                                <label><i data-lucide="hash"></i> {{ __('field.doc_pin') }}</label>
                                <input type="text" name="doc_pin" id="p_pin" class="form-control-modern" value="{{ $personal->doc_pin }}">
                            </div>
                            <div class="form-group-modern">
                                <label><i data-lucide="credit-card"></i> {{ __('field.doc_number') }}</label>
                                <input type="text" name="doc_number" id="p_num" class="form-control-modern" value="{{ $personal->doc_number }}">
                            </div>
                            <div class="form-group-modern">
                                <label><i data-lucide="file-text"></i> {{ __('field.doc_type') }}</label>
                                @php($doc_type = $user->userPersonal->doc_type??0)
                                <select class="form-control-modern" name="doc_type">
                                    <option value="1" @if($doc_type == 1) selected @endif>Passport</option>
                                    <option value="2" @if($doc_type == 2) selected @endif>ID Card</option>
                                </select>
                            </div>
                            <div class="form-group-modern">
                                <label><i data-lucide="calendar"></i> {{ __('field.birthday') }}</label>
                                <input type="date" name="birthday" class="form-control-modern" value="{{ date('Y-m-d', strtotime($personal->birthday??now())) }}">
                            </div>
                            <div class="form-group-modern">
                                <label><i data-lucide="users"></i> {{ __('field.gender') }}</label>
                                @php($gender = $user->userPersonal->gender??0)
                                <select class="form-control-modern" name="gender">
                                    <option value="1" @if($gender == 1) selected @endif>Erkak</option>
                                    <option value="2" @if($gender == 2) selected @endif>Ayol</option>
                                </select>
                            </div>
                            <div class="form-group-modern">
                                <label><i data-lucide="phone"></i> {{ __('field.phone') }}</label>
                                <input type="text" name="phone" maxlength="9" id="tel_number" class="form-control-modern" value="{{ $personal->phone }}">
                            </div>
                        </div>

                        @if($user->status == 1)
                            <div class="form-actions">
                                <button type="submit" class="btn-modern btn-primary">
                                    <i data-lucide="save"></i>
                                    <span>{{ __('action.save') }}</span>
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('#p_num').inputmask({mask: 'AA9999999'});
        $('#p_pin').inputmask({mask: '99999999999999'});
        $('#tel_number').inputmask({mask: '999999999'});

        function transliterate(element, field) {
            const value = element.value;
            const toTitleCase = (text) => text.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
            const toUzbekCyrillic = (text) => {
                const map = {'a':'а','b':'б','d':'д','e':'е','f':'ф','g':'г','h':'ҳ','i':'и','j':'ж','k':'к','l':'л','m':'м','n':'н','o':'о','p':'п','q':'қ','r':'р','s':'с','t':'т','u':'у','v':'в','x':'х','y':'й','z':'з',"o'":"ў","g'":"ғ","sh":"ш","ch":"ч","ng":"нг"};
                let result = text.toLowerCase().replace(/o'/g,'ў').replace(/g'/g,'ғ').replace(/sh/g,'ш').replace(/ch/g,'ч').replace(/ng/g,'нг');
                return toTitleCase(result.split('').map(char => map[char] || char).join(''));
            };
            const toRussian = (text) => {
                const map = {'a':'а','b':'б','d':'д','e':'е','f':'ф','g':'г','h':'х','i':'и','j':'ж','k':'к','l':'л','m':'м','n':'н','o':'о','p':'п','q':'к','r':'р','s':'с','t':'т','u':'у','v':'в','x':'х','y':'й','z':'з',"o'":"у","g'":"г","sh":"ш","ch":"ч","ng":"нг"};
                let result = text.toLowerCase().replace(/o'/g,'у').replace(/g'/g,'г').replace(/sh/g,'ш').replace(/ch/g,'ч').replace(/ng/g,'нг');
                return toTitleCase(result.split('').map(char => map[char] || char).join(''));
            };
            const toEnglish = (text) => toTitleCase(text.toLowerCase().replace(/o'/g,'o').replace(/g'/g,'g'));

            document.querySelector(`input[name=${field}_kr]`).value = toUzbekCyrillic(value);
            document.querySelector(`input[name=${field}_ru]`).value = toRussian(value);
            document.querySelector(`input[name=${field}_en]`).value = toEnglish(value);
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
