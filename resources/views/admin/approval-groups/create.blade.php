@extends('layouts.app')

@php
    $loc = session()->get('locale', app()->getLocale());
    $tt = ['uz'=>'Guruh yaratish','ru'=>'Создать группу','en'=>'Create group'];
    $pageTitle = $tt[$loc] ?? $tt['uz'];
    $group = null;
    $selectedUserIds = [];
@endphp

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/select2.css') }}" rel="stylesheet">

    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon"><i data-lucide="user-plus"></i></div>
                    <div class="header-text">
                        <h1>{{ $pageTitle }}</h1>
                    </div>
                </div>
                <div class="header-center">@include('templates.alert')</div>
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item"><i data-lucide="home"></i></a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('approval-groups.index', ['org' => $orgId]) }}" class="breadcrumb-item">
                        <span>{{ $loc === 'ru' ? 'Группы согласующих' : ($loc === 'en' ? 'Approval groups' : 'Kelishuvchilar guruhlari') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ $pageTitle }}</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">
            @include('admin.approval-groups._form', [
                'action'          => route('approval-groups.store'),
                'method'          => 'POST',
                'lockOrg'         => false,
                'selectedUserIds' => [],
            ])
        </div>
    </div>
@endsection
