 <!-- ======= Sidebar ======= -->
 <aside id="sidebar" class="sidebar">

     <ul class="sidebar-nav" id="sidebar-nav">

         <li class="nav-item">
             <a class="nav-link {{ Request::is('accurate/items') ? '' : 'collapsed' }}"
                 href="/accurate/items?dbId={{ request('dbId') }}">
                 <i class="bi bi-grid"></i>
                 <span>Data Barang</span>
             </a>
         </li><!-- End Dashboard Nav -->
         <li class="nav-item">
             <a class="nav-link {{ Request::is('accurate/analisa-harga-terakhir*') ? '' : 'collapsed' }}"
                 href="/accurate/analisa-harga-terakhir?dbId={{ request('dbId') }}">
                 <i class="bi bi-card-list"></i>
                 <span>Analisa barang</span>
             </a>
         </li><!-- End Dashboard Nav -->



     </ul>

 </aside><!-- End Sidebar-->
