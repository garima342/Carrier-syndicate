# InternHub — Company Listing Filters Update

## Completed
- Company search by company name, email, and recruiter name.
- Dropdown filters: Company Type, Industry, Company Size, Country, State, City, and Email Verification.
- Dropdown changes submit the filter form automatically; Search and Clear controls are available.
- Listing now displays company type, industry, size, combined location, recruiter, verification status, and join date.
- Matching record count and empty-results message.
- Prepared statements for filter values and HTML escaping for displayed values.
- Existing admin authentication, edit, delete, and CSRF form integration retained.

## Run locally
1. Extract the project into `C:/xampp/htdocs/`.
2. Start Apache and MySQL in XAMPP.
3. Ensure the `internhub_new` database is imported and configured in `admin/config/db.php`.
4. Open `http://localhost/internhubProject1/internhubProject/admin/companies.php` (adjust the URL if you extract the inner `internhubProject` folder directly).
5. Sign in with your existing admin account.

## Notes
- Email verification filter follows the provided schema values `Yes` and `No`.
- This package contains the updated project files. Test it against your local database before pushing to GitHub.
