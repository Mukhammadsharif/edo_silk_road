<ul>
    @foreach($childs as $key => $child)
        <li class="dep_id dep{{ $child->id }}" data-id="{{ $child->id }}">
            <div style="display: flex; align-items: center; gap: 0.5rem; justify-content: space-between;">
                <span style="flex: 1;">{{ $child->lang($child->id) }}</span>
                @if($child->status != 1)
                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 6px; font-size: 11px; font-weight: 600; color: #78350f;">
                        <i data-lucide="alert-circle" style="width: 12px; height: 12px; color: #f59e0b;"></i>
                        {{ __('field.inactive') }}
                    </span>
                @else
                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-radius: 6px; font-size: 11px; font-weight: 600; color: #065f46;">
                        <i data-lucide="check-circle" style="width: 12px; height: 12px; color: #10b981;"></i>
                    </span>
                @endif
            </div>
            @if(count($child->childs))
                @include('admin.organizations.manageChild', ['childs' => $child->childs])
            @endif
        </li>
    @endforeach
</ul>
