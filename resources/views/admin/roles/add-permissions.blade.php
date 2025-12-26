@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.roles') }}</h5>
                            <span>{{ __('menu.roles') }}</span>
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
                                <a href="{{ route('roles.index') }}">{{ __('menu.roles') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('admin/roles/'.$role->id.'/give-permissions') }}"
                              class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                @foreach($parentPermissions as $parent)
                                    <div class="col-sm-12 col-xl-4">
                                        <h4 class="sub-title">{{ $parent->name }}</h4>
                                        @foreach($parent->getChild as $child)
                                            <p><input type="checkbox" name="permission[]" value="{{ $child->name }}"
                                                      class="js-switch"
                                                      id="roleID{{ $child->id }}" {{ in_array($child->id, $rolePermissions) ? 'checked':'' }} /> {{ $child->name??'-' }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endforeach
                                <div class="col-lg-12">
                                    <div class="justify-content-end">
                                        <a href="{{ url('admin/roles') }}" class="btn btn-light">
                                            <i class="ik ik-x"></i>{{ __('action.cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ik ik-save"></i>{{ __('action.save') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
