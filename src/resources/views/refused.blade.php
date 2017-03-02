<?php
/**
 * refused.blade.php
 *
 * Author: topster21
 * Github: @see github.com/topster21/lmao
 * Date: 2-3-17
 * Time: 10:38
 *
 *
 *
 */
?>
@extends('template.master')

@section('page-title', 'Refused')

@section('page-content')
<h2>The server has refused our request...</h2>
<p>Please make sure your account has access</p>

<a href='/lmao/initiate'><button>Try again</button></a>

<a href='/'><button>Return to home</button></a>
@endsection