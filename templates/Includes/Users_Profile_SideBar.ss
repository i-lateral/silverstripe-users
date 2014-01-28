<div class="unit-25">
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
</div>
