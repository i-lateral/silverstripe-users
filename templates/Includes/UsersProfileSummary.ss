<% with $CurrentUser %>
    <div class="users-profile-summary">
        <p>
            <strong><% _t('Member.FIRSTNAME',"First Name") %></strong> $FirstName<br/>
            <strong><% _t('Member.SURNAME',"Surname") %></strong> $Surname<br/>
            <strong><% _t("Member.EMAIL","Email") %></strong> $Email<br/>
        </p>
    </div>
<% end_with %>