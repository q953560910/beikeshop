@extends('errors.layout')
@section('title', __('common.error_page'))

@section('content')
  <x-shop-no-data text="{{ __('common.error_page') }}" link="javascript:history.go(-1)" btn="{{ __('common.error_page_btn') }}" />
@endsection
