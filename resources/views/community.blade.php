<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('front/style.css') }}">
    <title>Dashboard</title>
</head>
<body>
    <div class="menu">
        <ul>
            <li class="profile">
                <div class="img-box">
                    <img src="{{ asset('front/images/user-200x300.webp') }}" alt="profile">
                </div>
                <h2>Ahmed AbdElfattah</h2>
            </li>

            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="fas fa-users"></i>
                    <p>Users</p>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fas fa-table"></i>
                    <p>Sessions</p>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fas fa-chart-line"></i>
                    <p>Plans</p>
                </a>
            </li>

            <li>
            <a href="{{ route('communities') }}" class="{{ request()->is('community') ? 'active' : '' }}">
            <i class="fas fa-user-group"></i>
                    <p>Communities</p>
                </a>
            </li>
            
            <li class="log-out">
                <a href="#">
                    <i class="fas fa-sign-out"></i>
                    <p>Log out</p>
                </a>
            </li>

        </ul>
    </div>

    <div class="content">

        <div class="title-info">
            <p>Create Community</p>
            <i class="fas fa-edit"></i>
        </div>
        

        <form action="https://calmletics-production.up.railway.app/{{ route('communities.store') }}" method="POST" class="community-form">
        @csrf

        @if (session('success'))
    <div class="success-message">
        {{ session('success') }}
        <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
@endif


        <div class="input-box">
            <label for="name">Community Name</label>
            <input type="text" id="name" name="name" placeholder="Enter community name">
            @error('name')
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-box">
            <label for="description">Community Level</label>
            <input type="text" id="level" name="level" placeholder="Enter community level">
            @error('level')
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-box">
            <label for="code">Plan Id</label>
            <input type="text" id="plan_id" name="plan_id" placeholder="Enter Plan Id">
            @error('plan_id')
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="submit-link">
    <i class="fas fa-paper-plane"></i> Submit
</button>

    </form>

    </div>
</body>
</html>