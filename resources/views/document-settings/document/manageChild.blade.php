<ul>
    @foreach($childs as $key => $child)
        <li>
            <div class="tree-item-content">
                <span class="order-number-badge">{{ $child->order_number }}</span>
                <span class="tree-item-text" style="font-style: italic;">{{ $child->lang($child->lang_id) }}</span>
                <span class="tree-item-code">{{ $child->doc_code }}</span>

                <div class="tree-item-actions">
                    <a href="{{ route('document-type.edit', $child->id) }}" class="tree-edit-btn">
                        <i data-lucide="square-pen"></i>
                    </a>
                </div>
            </div>
            @if(count($child->childs))
                @include('document-settings.document.manageChild', ['childs' => $child->childs])
            @endif
        </li>
    @endforeach
</ul>
