@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('/departments/treeview.css') }}">
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="fa fa-sitemap bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('structure') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('structure') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12 col-sm-12">
                <h3>{{ __('structure') }}</h3>
                <ul id="tree1">
                    @foreach($departments as $key => $department)
                        <li class="dep_id dep{{ $department->id }}" data-id="{{$department->id}}">
                            {{ $key+1 }}.
                            @if(session()->get('locale') === 'ru')
                                {{ $department->dep_name_ru }}
                            @elseif(session()->get('locale') === 'en')
                                {{ $department->dep_name_en }}
                            @else
                                {{ $department->dep_name }}
                            @endif
                            @if(count($department->childs))
                                @include('admin.departments.manageChild',['childs' => $department->childs])
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="card-body">
                    <div class="card-header">
                        <h3>{{ __('employees') }}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-text">
                            <thead>
                            <tr>
                                <th>{{ __('#') }}</th>
                                <th>{{ __('fam_name') }}</th>
                                <th>{{ __('user_posts') }}</th>
                                <th>{{ __('status') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="{{ asset('/departments/treeview.js') }}"></script>
    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.dep_id').on("click",function(e) {

            e.stopPropagation()

            let dep_id = $(this).data("id");

            $('#dep_id').val(dep_id)

            $('.dep_id').removeClass(' text-danger');
            $('.dep'+dep_id).addClass(' text-danger');

            $.ajax({
                url: '/hr/structure/get-users/'+dep_id,
                type: 'GET',
                dataType: 'json',
                beforeSend: function(){
                    $( "#editIsActive1" ).prop( "checked", false )
                    $( "#editIsActive2" ).prop( "checked", false )
                },
                success: function(res){
                    $('tbody').empty();
                    let userData = '';
                    let num = 0;
                    let col = '';
                    let m_name = '';
                    if (res) {
                        $.each(res, function(item) {
                            num+= 1;
                            col = res[item];
                            m_name = col.personal.m_name;
                            if (!m_name) {
                                m_name = '';
                            }

                            let isActive = "{{ __('passive') }}";
                            if (col.isActive === 'A') {
                                isActive = "{{ __('active') }}";
                            }

                            userData+= '<tr>';
                            userData+= '<td>'+num+'</td>';
                            userData+= '<td>'+col.personal.l_name+' '+col.personal.f_name+' '+m_name+'</td>';
                            userData+= '<td>'+col.user_post.post_name+'</td>';
                            userData+= '<td>'+isActive+'</td>';
                            userData+= '</tr>';
                        });
                    }
                    $('tbody').append(userData);
                },
                complete:function(res){
                }

            })

        })
    </script>


@endsection
