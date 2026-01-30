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
                        <h3><i class="ik ik-heart-on"></i> {{ __('menu.emp_fam') }}</h3>
                    </div>
                    <div class="card-body template-demo">
                        @if($user->status == 1)
                            <a href="{{ route('family.show', $user->id) }}" class="btn btn-primary">
                                <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                            </a>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr class="text-center">
                                <th>{{ __('field.#') }}</th>
                                <th>{{ __('field.fam_code') }}</th>
                                <th>{{ __('field.fam_name') }}</th>
                                <th>{{ __('field.br_date') }}</th>
                                <th>{{ __('field.live_address') }}</th>
                                <th>{{ __('field.post_address') }}</th>
                                <th>{{ __('field.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $key => $item)
                                <tr class="text-center">
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        <span class="badge badge-pill bg-light-blue">
                                            {{ $item->familyName($item->fam_code) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->fam_name }}</td>
                                    <td>{{ date('d.m.Y', strtotime($item->br_date)) }}</td>
                                    <td>{{ $item->live_address }}</td>
                                    <td>{{ $item->post_address }}</td>
                                    <td>
                                        @if($user->status == 1)
                                            <a href="{{ route('family.edit', $item->id) }}"
                                               class="btn btn-icon btn-outline-info">
                                                <i class="ik ik-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('field.not_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
