<header>
    <div class="container-fluid position-relative no-side-padding">

        <a href="{{ route('front.home')}}" class="logo"><img src="{{ asset('frontend/images/logo.png') }}" alt="Logo Image"></a>

        <div class="menu-nav-icon" data-nav-menu="#main-menu"><i class="ion-navicon"></i></div>

        <ul class="main-menu visible-on-click" id="main-menu">
            <li><a href="{{ route('front.home')}}">Home</a></li>
            {{-- <li><a href="#">Categories</a></li> --}}
            <li><a href="{{ route('front.all.post')}}">All Posts</a></li>
            @guest
                <li><a href="{{ route('login')}}">Login</a></li>
            @else 
                <li><a href="{{ route('login')}}">Logged In</a></li>
            @endguest
            <li><a href="{{ route('register')}}">Register</a></li>
        </ul><!-- main-menu -->

        <div class="src-area">
        <form method="GET" action="{{ route('search')}}"> 
                <button class="src-btn" type="submit"><i class="ion-ios-search-strong"></i></button>
                <input class="src-input" type="text" name="query" placeholder="Type of search" value="{{ isset($query)? $query: '' }}">
            </form>
        </div>

    </div><!-- conatiner -->
</header>