<div class="col-sm-12">
    <label class="to_org_for_sent_doc">{{ __('document.organization') }} <span class="text-red">*</span></label><br>
    <select name="doc_org" class="form-control select2" style="width: 400px">
        @foreach($doc_organizations as $key => $doc_organization)
            <option value="{{ $doc_organization->id }}">{{ $key+1 }}.
                {{ $doc_organization->lang($doc_organization->id) }}
            </option>
        @endforeach
    </select>
</div>
