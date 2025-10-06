<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('front/style.css') }}">
    <link rel="stylesheet" href="{{ asset('front/dashboard.css') }}">
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
            <p>Dashboard</p>
            <i class="fas fa-bars"></i>
        </div>

        <div class="data-info">
            <div class="box">
                <i class="fas fa-user"></i>
                <div class="data">
                    <p>Users</p>
                    <span>{{ count($users) }}</span>
                </div>
            </div>

            <div class="box">
                <i class="fas fa-table"></i>
                <div class="data">
                    <p>Sessions</p>
                    <span>100</span>
                </div>
            </div>

            <div class="box">
                <i class="fas fa-chart-line"></i>
                <div class="data">
                    <p>Plans</p>
                    <span>100</span>
                </div>
            </div>

            <div class="box">
                <i class="fas fa-user-group"></i>
                <div class="data">
                    <p>Communities</p>
                    <span>100</span>
                </div>
            </div>
        </div>

        <div class="title-info">
            <p>Users</p>
            <i class="fas fa-table"></i>
        </div>

        <table>
            <thead>
                <tr>
                    <th>
                        User-Name
                    </th>

                    <th>
                        User-Email
                    </th>

                    <th>
                        User-Com
                    </th>

                    <th>
                        User-Code
                    </th>

                    <th>
                        Role    
                   </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>
                        {{ $user->name }}
                    </td>

                    <td>
                        {{ $user->email }}                    
                    </td>

                    <td>
                        <span class="com">
                            @if ($user->com_free_id !== null)
                                {{ ($user->comfree->name)." "."===>"." "."Com-Free"}}
                            @elseif ($user->com_pre_id !== null)
                                {{($user->compre->name)." "."===>"." "."Com-Pre"}}
                            @else
                                No Community    
                            @endif

                        </span>
                    </td>

                    <td>
                       <span class="code">{{ $user->code }}</span> 
                    </td>

                    <td>
                       <span class="role">
                        
                        @if ($user->role == '0')
                            User
                        @else
                            Admin
                        @endif

                       </span> 
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</body>
</html>