<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="{{route('home')}}" class="brand-link">
    <img src="/images/admin_images/logo.png" alt="MIQR Logo" class="brand-image-xl"
      style="max-height: 55px; width: 100%;">
    <br>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    @auth
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel text-center">
      <div class="info">
        <a href="{{route ('profile')}}" class="d-block text-uppercase">{{Auth::user()->vorname}}
          {{Auth::user()->name}}</a>
        <ul class="list-unstyled">
          @foreach ((Auth::user()->roles->pluck('name')) as $item)
          <li>
            <a href="{{route ('profile')}}" class="d-block small">{{ $item }}</a>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
    @endauth


    <!-- Sidebar Menu -->
    <nav class="mt-4">
      <ul class="nav nav-pills nav-sidebar flex-column text-uppercase" data-widget="treeview" role="menu"
        data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
        <li class="nav-item has-treeview">
          <a href="{{(url('/dashboard')) }}" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt" style="color: #B17A57"></i>
            <p>
              Dashboard
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('umfrages.index') }}" class="nav-link">
            <i class="fa-brands fa-wpforms nav-icon" style="color:#E5D0E3;"></i>
            <p>Evaluationen</p>
          </a>
        </li>
        @if(auth()->check() && (auth()->user()->hasRole('Super_Admin') || auth()->user()->id == 39))
        <li class="nav-item">
          <a href="{{ route('documents.index') }}" class="nav-link">
            <i class="fa-solid fa-feather-pointed nav-icon" style="color:#ff9999;"></i>
            <p>Dokumente</p>
          </a>
        </li>
        @endif
        <li class="nav-item has-treeview">
          <a href="{{ url('/settings') }}" class="nav-link">
            <i class="nav-icon fas fa-cogs" style="color: #5bc0de"></i>
            <p>
              Einstellungen
            </p>
          </a>
        </li>
        <li class="nav-item has-treeview">
          <a href="{{ url('/inventory') }}" class="nav-link">
            <i class="nav-icon fas fa-boxes" style="color:#f0ad4e;"></i>
            <p>
              Inventar
            </p>
          </a>
        </li>
        @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Super_Admin')))
        <li class="nav-item has-treeview">
          <a href="{{ url('/contacts') }}" class="nav-link">
            <i class="nav-icon fas fa-address-book" style="color:#6969B3;"></i>
            <p>
              MIQR Mitarbeiter
            </p>
          </a>
        </li>
        @endif
        @if(auth()->check() && (auth()->user()->hasRole('Terminal') || auth()->user()->hasRole('Super_Admin')))
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fa-solid fa-briefcase" style="color:green;"></i>
            <p>
              Übungsfirmen
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview text-capitalize">
            <li class="nav-item has-treeview">
              <!-- <a href="#" class="nav-link">
                <i class="nav-icon fas fa-circle"></i>
                <p>
                  Berlin
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a> -->
              <ul class="nav nav-treeview font-weight-light">
                <li class="nav-item">
                  <a href="{{ route('practice-companies.city', 'Berlin') }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>B-Berlin</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-circle"></i>
                <p>
                  Chemnitz
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview font-weight-light">
                <li class="nav-item">
                  <a href="{{ route('practice-companies.city', 'Chemnitz') }}" class="nav-link">
                    <i class="fa-solid fa-address-card nav-icon" style="color:darkturquoise"></i>
                    <p>Profil & E-mail</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('practice-companies.lex', 'Chemnitz') }}" class="nav-link">
                    <i class="fa-solid fa-l nav-icon" style="color:yellow;"></i>
                    <p>Lexware</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-circle"></i>
                <p>
                  Dresden
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview font-weight-light">
                <li class="nav-item">
                  <a href="{{ route('practice-companies.city', 'Dresden') }}" class="nav-link">
                    <i class="fa-solid fa-address-card nav-icon" style="color:darkturquoise"></i>
                    <p>Profil & E-mail</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('practice-companies.lex', 'Dresden') }}" class="nav-link">
                    <i class="fa-solid fa-l nav-icon" style="color:yellow;"></i>
                    <p>Lexware</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-circle"></i>
                <p>
                  Erfurt
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview font-weight-light">
                <li class="nav-item">
                  <a href="{{ route('practice-companies.city', 'Erfurt') }}" class="nav-link">
                    <i class="fa-solid fa-address-card nav-icon" style="color:darkturquoise"></i>
                    <p>Profil & E-mail</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('practice-companies.lex', 'Erfurt') }}" class="nav-link">
                    <i class="fa-solid fa-l nav-icon" style="color:yellow;"></i>
                    <p>Lexware</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-circle"></i>
                <p>
                  Leipzig
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview font-weight-light">
                <li class="nav-item">
                  <a href="{{ route('practice-companies.city', 'Leipzig') }}" class="nav-link">
                    <i class="fa-solid fa-address-card nav-icon" style="color:darkturquoise"></i>
                    <p>Profil & E-mail</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('practice-companies.lex', 'Leipzig') }}" class="nav-link">
                    <i class="fa-solid fa-l nav-icon" style="color:yellow;"></i>
                    <p>Lexware</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-circle"></i>
                <p>
                  Suhl
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview font-weight-light">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Blücher6</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Puschkin1</p>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </li>
        @endif
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-ticket-alt" style="color:orangered;"></i>
            <p>
              Ticket
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview text-capitalize">
            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Super_Admin')))
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="fas fa-laptop-code nav-icon"></i>
                <p>
                  IT
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview font-weight-light">
                <li class="nav-item">
                  <a href="{{ route('ticket.opentickets') }}" class="nav-link">
                    <i class="far fa-folder-open nav-icon"></i>
                    <p>Offen</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('ticket.unassigned') }}" class="nav-link">
                    <i class="fas fa-exclamation-triangle nav-icon"></i>
                    <p>Nichtzugewissen</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="" class="nav-link">
                    <i class="far fa-bell nav-icon"></i>
                    <p>Kürzlich aktualisiert</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('ticket.history') }}" class="nav-link">
                    <i class="far fa-folder nav-icon"></i>
                    <i class="fas fa-check nav-icon"></i>
                    <p>Erledigt</p>
                  </a>
                </li>
              </ul>
            </li>
            @endif
            <li class="nav-item">
              <a href="{{ route('ticket.index') }}" class="nav-link">
                <i class="fas fa-marker nav-icon" style="color:#E5D0E3;"></i>
                <p>Ticket Erstellen</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('/usertickets') }}" class="nav-link">
                <i class="far fa-list-alt nav-icon" style="color:#9FFFCB;"></i>
                <p>Meine Tickets</p>
              </a>
            </li>
          </ul>
        </li>
        @if(auth()->check() && auth()->user()->hasRole('Super_Admin'))
        <li class="nav-item has-treeview">
          <a href="{{route ('special-tickets.index')}}" class="nav-link">
            <i class="nav-icon fas fa-earth-americas" style="color:#5aa9d1;"></i>
            <p>
              Standort Tickets
            </p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasRole('Super_Admin'))
        <li class="nav-item has-treeview">
          <a href="{{route ('tasks.index')}}" class="nav-link">
            <i class="nav-icon fas fa-desktop" style="color:#d97706;"></i>
            <p>
              Bedarf
            </p>
          </a>
        </li>
        @endif
        @if(auth()->check() && auth()->user()->hasRole('Super_Admin'))
        <li class="nav-item has-treeview">
          <a href="{{route ('projects.index')}}" class="nav-link">
            <i class="nav-icon fas fa-project-diagram" style="color:#E0FF4F;"></i>
            <p>
              Project
            </p>
          </a>
        </li>
        @endif
        @if(auth()->check() && auth()->user()->hasAnyRole(['Super_Admin','Verwaltung']))
        <li class="nav-item has-treeview">
          <a href="{{route ('participants.index')}}" class="nav-link">
            <i class="nav-icon fa-solid fa-users" style="color:#55917F;"></i>
            <p>
              Teilnehmer Liste
            </p>
          </a>
        </li>
        @endif
        <li class="nav-item has-treeview">
          <a href="{{ route ('korso_index') }}" rel="noopener noreferrer" class="nav-link">
            <i class="nav-icon fa-solid fa-people-group" style="color: #65A30D"></i>
            <p>
              Korso Ticket
            </p>
          </a>
        </li>
        @if(auth()->check() && auth()->user()->hasAnyRole(['Super_Admin','HR']))
        <li class="nav-item has-treeview">
          <a href="{{route ('employees.index')}}" class="nav-link">
            <i class="nav-icon fa-solid fa-user-group" style="color: #3f6212"></i>
            <p>
              Mitarbeiter Liste
            </p>
          </a>
        </li>
        @endif
        @if(auth()->check() && auth()->user()->hasAnyRole(['Super_Admin','Verwaltung','handwerk_admin','handwerk']))
        <li class="nav-item has-treeview">
          <a href="{{route ('handwerk_index')}}" class="nav-link">
            <i class="nav-icon fa-solid fa-person-digging" style="color: #004873"></i>
            <p>
              Handwerkaufgaben
            </p>
          </a>
        </li>
        @endif
        <li class="nav-item has-treeview">
          <a href="{{route('video')}}" class="nav-link">
            <img src="/images/admin_images/help3.gif" class="nav-icon"
              style="height: 100 !important; width: 100 !important;" alt="" />
            <p>
              Hilfe
            </p>
          </a>
        </li>


        <!-- <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-circle"></i>
              <p>
                Erfurt
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Level 2</p>
                </a>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Level 2
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Level 3</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Level 3</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Level 3</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Level 2</p>
                </a>
              </li>
            </ul>
          </li> -->
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>