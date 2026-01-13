<div class="form-group-modern">
    <label>
        <i data-lucide="building-2"></i>
        {{ __('document.organization') }}
        <span class="required">*</span>
    </label>
    <select name="doc_org" class="form-control-modern select2" required>
        <option value="">{{ __('action.choose') }}</option>
        @foreach($doc_organizations as $key => $doc_organization)
            <option value="{{ $doc_organization->id }}">
                {{ $key+1 }}. {{ $doc_organization->lang($doc_organization->id) }}
            </option>
        @endforeach
    </select>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/templates/doc-organizations.css') }}">
