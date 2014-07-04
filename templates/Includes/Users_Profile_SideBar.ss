<aside class="users-sidebar unit-25 unit size1of4">
    <nav class="nav-v secondary">
        <ul>
            <% loop $AccountMenu %>
                <li>
                    <a href="$Link">
                        <span class="arrow">&rarr;</span>
                        <span class="text">$Title</span>
                    </a>
                </li>
            <% end_loop %>
        </ul>
    </nav>
</aside>
