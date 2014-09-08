<div class="units-row users-account line">
    <% include Users_Profile_SideBar %>

    <div class="users-content-container typography unit-75 unit size3of4 lastUnit">
        <% if $RequireVerification %>
            <div class="message message-bad">
                <p>
                    <% _t("Users.NotVerified", "You have not verified your email address") %>
                    <a href="{$BaseHref}users/register/sendverification">
                        <% _t("Users.Send", "Send now") %>
                    </a>
                </p>
            </div>
        <% end_if %>

        <h1><%t Security.ChangeYourPassword "Change your password" %></h1>

        $Form
    </div>
</div>
