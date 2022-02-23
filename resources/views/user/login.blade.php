
@extends('layout.master')
@section('title')
Login
@stop
@section('content')
<div class="global_page global_auth_page global_auth_login_page trainer">
    <div class="page_wrapper">
        <div class="background_image_wrapper">
            <div class="background_image"></div>
            <img class="background_logo" src="/img/logo_stroke.20317a00.png" alt="">
            <img class="background_line" src="/img/yellow_line.92625b72.svg" alt="">
        </div>
        <div class="main_wrapper">
            <div class="form_wrapper">
                <form class="el-form el-form--label-top full_page">
                    <div class="page_logo">
                        <img src="/img/logo_solid.82b0afe4.svg" alt="">
                    </div>
                    <div class="page_title"> Welcome Trainer, Please login first to enter the editing rooms
                    </div>
                    <div class="el-form-item is-success">
                        <label for="email" class="el-form-item__label">Email</label>
                        <div class="el-form-item__content">
                            <div class="el-input el-input--prefix">
                                <input class="el-input__inner" type="email" autocomplete="off" placeholder="Enter your email">
                                <span class="el-input__prefix">
                                    <span class="el-input__icon rava_icons rava_icon-Profile"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="el-form-item">
                        <label for="password" class="el-form-item__label">Password</label>
                        <div class="el-form-item__content">
                            <div class="el-input el-input--prefix el-input--suffix">
                                <input class="el-input__inner" type="password" autocomplete="off" placeholder="Enter your password">
                                <span class="el-input__prefix">
                                    <span class="el-input__icon rava_icons rava_icon-Privacy"></span>
                                </span>
                                <span class="el-input__suffix">
                                    <span class="el-input__suffix-inner"></span>
                                </span>
                            </div>
                            <div class="el-form-item__tip tip_bottom"> The password must be at least 6 characters long and contain at least 1 digit and 1 alphabetic character.
                            </div>
                        </div>
                    </div>
                    <div class="password_reset">
                        <button class="el-button el-button--link is-disabled password_reset" disabled="" type="button">
                            <span>
                                <span>Forgot password?</span>
                            </span>
                        </button>
                    </div>
                    <div class="el-form-item button_submit">
                        <div class="el-form-item__content">
                            <button class="el-button el-button--yellow_white is-disabled submit" type="button" disabled="">
                                <span> login </span>
                            </button>
                        </div>
                    </div>
                    <div class="register_link"> Don’t have an account?
                        <button class="el-button el-button--link password_reset" type="button">
                            <span>
                                <span>Register as a trainer here</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
