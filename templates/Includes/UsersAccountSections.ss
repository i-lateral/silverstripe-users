<% loop $Sections %>
    <h2>$Title</h2>

    <div class="section-content">$Content</div>

    <% if not $Last %><hr/><% end_if %>
<% end_loop %>