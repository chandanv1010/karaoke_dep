<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>LARAVEL CMS 01</title>

        <link href="{{ asset('backend/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('backend/font-awesome/css/font-awesome.css') }}" rel="stylesheet">

        <link href="{{ asset('backend/css/animate.css') }}" rel="stylesheet">
        <link href="{{ asset('backend/css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('backend/css/customize.css') }}" rel="stylesheet">

    </head>

    <body class="gray-bg">

        <div class="loginColumns animated fadeInDown">
            <div class="row">

                {{-- Truoc day cho nay con nguyen chu demo cua theme admin:
                     "Welcome to IN+" kem ba doan Lorem Ipsum. Thay bang ten cong
                     ty lay tu Cau hinh > He thong, doi ten cong ty la trang nay
                     tu doi theo. --}}
                <div class="col-md-6">
                    <h2 class="font-bold">{{ company_name() }}</h2>

                    <p>
                        Trang quản trị nội dung website.
                    </p>

                    <p>
                        Đăng nhập bằng tài khoản được cấp để quản lý sản phẩm, bài viết,
                        đơn liên hệ và các cấu hình của website.
                    </p>

                    <p>
                        <small>Nếu quên mật khẩu hoặc cần cấp thêm tài khoản, vui lòng liên hệ quản trị viên.</small>
                    </p>

                </div>
                <div class="col-md-6">
                    <div class="ibox-content">
                      
                        <form method="post" class="m-t" role="form" action="{{ route('auth.login') }}">
                            @csrf
                            <div class="form-group">
                                <input 
                                    type="text" 
                                    name="email"
                                    class="form-control" 
                                    placeholder="Email" 
                                    value="{{ old('email') }}"
                                >
                                @if ($errors->has('email'))
                                    <span class="error-message">* {{ $errors->first('email') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <input 
                                    type="password" 
                                    name="password"
                                    class="form-control" 
                                    placeholder="Password" 
                                >
                                @if ($errors->has('password'))
                                    <span class="error-message">* {{ $errors->first('password') }}</span>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-success block full-width m-b">Đăng nhập</button>

                            <a href="#">
                                <small>Forgot password?</small>
                            </a>
                        </form>
                        <p class="m-t">
                            <small>Newbie Code we app framework base on Bootstrap 3 &copy; 2014</small>
                        </p>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-md-6">
                    Copyright Example Company
                </div>
                <div class="col-md-6 text-right">
                <small>© 2023</small>
                </div>
            </div>
        </div>

    </body>

</html>
