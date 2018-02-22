<% if $Verify %>
    <p><% _t('Users.VerifiedMessage','Your account has been now been verified.') %></p>
<% else %>
    <p><% _t('Users.NotVerifiedMessage','Your account could not be verified.') %></p>
<% end_if %>

<% if $CurrentMember %>
    <p>
        <a class="btn" href="{$BaseHref}users/account">
            <% _t("Users.BackToAccount", "Back to your account") %>
        </a>
    </p>
<% end_if %>