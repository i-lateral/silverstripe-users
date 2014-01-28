<div class="users-accountnav nav-h">
    <ul>
        <% if $CurrentMember %>
            <li class="details">
                <% _t("Users.HELLO", "Hi") %>
                $CurrentMember.FirstName</li>
            <li class="account">
                <a href="{$BaseHref}users/account">
                    <% _t("Users.MYACCOUNT", "My account") %>
                </a>
            </li>
            <li class="logout">
                <a href="{$BaseHref}Security/logout?BackURL={$BaseHref}">
                    <% _t("Users.LOGOUT", "Logout") %>
                </a>
            </li>
        <% else %>
            <li class="signin">
                <a href="{$BaseHref}Security/login?BackURL={$Link}">
                    <% _t("Users.LOGIN", "Log in") %>
                </a>
            </li>
            <li class="register">
                <a href="{$BaseHref}users/register">
                    <% _t("Users.REGISTER", "Register") %>
                </a>
            </li>
        <% end_if %>
    </ul>
</div>
