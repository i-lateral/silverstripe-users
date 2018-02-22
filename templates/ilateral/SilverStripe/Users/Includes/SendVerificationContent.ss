<% if $Sent %>
    <p><% _t('Users.VerificationSent','A verification has been sent to your registered email address.') %></p>
    <p><% _t('Users.NextSteps','You will receive an email with a verification link. Clicking the link will verify your account.') %></p>
<% else %>
    <p><% _t('Users.NotVerificationSent','Unable to send verification.') %></p>
<% end_if %>

<% if $CurrentMember %>
    <p>
        <a class="btn" href="{$BaseHref}users/account">
            <% _t("Users.BackToAccount", "Back to your account") %>
        </a>
    </p>
<% end_if %>