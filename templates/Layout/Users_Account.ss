<div class="units-row users-account">
    <% include Users_Profile_SideBar %>

    <div class="content-container typography unit-75">

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

        <h1>$Title</h1>

        $Content

        $Form
    </div>
</div>
