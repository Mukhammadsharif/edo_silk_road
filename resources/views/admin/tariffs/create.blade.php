@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-header-title">
                        <i class="ik ik-plus-circle bg-blue"></i>
                        <h5>{{ __('action.create') }} {{ __('menu.tariff') }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        @include('templates.alert')
                        <form action="{{ route('tariffs.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.code') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.max_users') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="max_users" class="form-control" value="{{ old('max_users') }}" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.max_documents') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="max_documents" class="form-control" value="{{ old('max_documents') }}" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.price_monthly') }} (so'm)</label>
                                        <input type="number" name="price_monthly" class="form-control" value="{{ old('price_monthly') }}" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.price_yearly') }} (so'm)</label>
                                        <input type="number" name="price_yearly" class="form-control" value="{{ old('price_yearly') }}" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.status') }}</label>
                                        <select name="status" class="form-control">
                                            <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Faol</option>
                                            <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>No faol</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mt-3">
                                <a href="{{ route('tariffs.index') }}" class="btn btn-light">{{ __('action.cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('action.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
