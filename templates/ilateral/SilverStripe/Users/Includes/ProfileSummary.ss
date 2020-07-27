<% with $CurrentUser %>
    <div class="users-profile-summary">
        <p>
            <strong><%t SilverStripe\Security\Member.Member.FIRSTNAME "First Name" %></strong> $FirstName<br/>
            <strong><%t SilverStripe\Security\Member.Member.SURNAME "Surname" %></strong> $Surname<br/>
            <strong><%t SilverStripe\Security\Member.Member.EMAIL "Email" %></strong> $Email<br/>
            <strong><%t Users.FirstRegistered "First Registered" %></strong> $Created.Ago<br/>
        </p>
    </div>
<% end_with %>