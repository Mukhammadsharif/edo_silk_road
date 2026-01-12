<ul>
    @foreach($childs as $key => $child)
        <li class="dep_id dep{{ $child->id }}" data-id="{{$child->id}}">
            {{ $child->lang($child->id) }}
            <i class="ik {{ ($child->status == 1 ? "":"ik-alert-circle text-warning") }}"></i>
            @if(count($child->childs))
                @include('admin.organizations.manageChild',['childs' => $child->childs])
            @endif
        </li>
    @endforeach
</ul>
