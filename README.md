# MHLW-Compliant Stress Check System

A WordPress plugin implementing the "Stress Check System based on the Industrial Safety and Health Act" established by the Ministry of Health, Labour and Welfare (MHLW) of Japan.

## Overview

This plugin provides a complete, legally compliant stress check assessment system for internal corporate use, featuring:

- 57-item standardized stress check questionnaire
- Automated scoring with reverse-scoring support
- High-stress individual identification based on MHLW criteria
- Role-based access control (Industrial Physicians vs Department Managers)
- Group analysis with 10-person minimum rule
- PDF result generation
- Employee CSV import
- Data encryption and security features

## Installation

1. Upload the plugin files to `/wp-content/plugins/mhlw-compliant-stress-check-system/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create necessary database tables and roles

## System Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher / MariaDB 10.0 or higher
- SSL/TLS certificate (HTTPS required)

## User Roles

The plugin creates three custom WordPress roles:

### 1. Administration - Industrial Physician/Designated Personnel
- **Capabilities:**
  - View individual response details
  - View individual judgment results
  - View/download individual result PDFs
  - Provide follow-up to high-stress individuals
  - View group analysis results
  - View response progress
  - Import employees via CSV

### 2. General Administration - Department Manager
- **Capabilities:**
  - View group analysis results (10+ respondents only)
  - View response progress (participation rates)
  - Import employees via CSV
- **Restrictions:**
  - Cannot access individual response contents
  - Cannot view individual judgment results
  - Cannot download individual PDFs
  - Cannot access personally identifiable information

### 3. Stress Check Employee
- **Capabilities:**
  - Take stress check assessment
  - View own results
  - Download own result PDF
- **Restrictions:**
  - Cannot access wp-admin
  - Cannot view other users' data

## Usage

### For Administrators

#### 1. Import Employees
1. Navigate to **Stress Check > Import Employees**
2. Prepare a CSV file with columns: Employee ID, Name, Department ID, Department Name, Org Level 1, Org Level 2, Org Level 3
3. Upload the CSV file
4. Review import results

#### 2. View Dashboard
- Navigate to **Stress Check > Dashboard**
- View overall statistics: total employees, completion rate, high-stress count
- Monitor recent activity

#### 3. Group Analysis
- Navigate to **Stress Check > Group Analysis**
- Select analysis unit: Company-wide, Department, or Organization Level
- View statistics and charts (only displayed for groups with 10+ respondents)

#### 4. Individual Results (Implementation Administrators only)
- Navigate to **Stress Check > Individual Results**
- Search by Employee ID or Name
- Filter by stress status
- View detailed results and download PDFs

### For Employees

#### Taking the Stress Check
1. Log in with employee credentials
2. Navigate to the page with `[mhlw_stress_check_form]` shortcode
3. Answer all 57 questions (4-point scale: Yes/Mostly Yes/Slightly No/No)
4. Use "Save Draft" to save progress and resume later
5. Submit when all questions are answered

#### Viewing Results
1. Navigate to the page with `[mhlw_my_results]` shortcode
2. View classification (High-Stress/Not Applicable)
3. Review domain and scale scores
4. Download PDF report

## Shortcodes

### `[mhlw_stress_check_form]`
Displays the 57-question stress check assessment form.

### `[mhlw_my_results]`
Displays the user's own stress check results with charts and PDF download option.

## Scoring Methodology

### Domains
- **Domain A (Items 1-17):** Job Stressors - Workload, job control, work environment
- **Domain B (Items 18-46):** Stress Reactions - Physical and psychological symptoms
- **Domain C (Items 47-57):** Social Support & Other Factors - Support from supervisors, coworkers, family

### Reverse-Scored Items
Items where "No" indicates stress and "Yes" indicates no stress are reverse-scored:
- Domain A: Items 8, 9, 10, 14, 16, 17
- Domain B: Items 18, 19, 20, 41, 42, 43, 44, 45, 46
- Domain C: Items 47, 48, 49, 50, 51, 53, 54, 56, 57

### High-Stress Criteria
A respondent is classified as "High-Stress Individual" if either:
- **Criterion (a):** Domain B score ≥ 77 (out of 116)
- **Criterion (b):** Combined Domain A+C score ≥ 76 (out of 104) AND Domain B score ≥ 63

### Scales for Group Analysis
- Psychological Job Demand
- Physical Job Demand
- Job Control
- Skill Utilization
- Workplace Environment
- Job Satisfaction
- Vigor
- Fatigue
- Anger
- Anxiety
- Depression
- Physical Symptoms
- Supervisor Support
- Coworker Support
- Family and Friend Support

## Security Features

### Data Protection
- Individual response data is encrypted in the database
- Draft responses are encrypted
- Temporary PDF download URLs with expiration (1 hour)

### Access Control
- Login attempt limiting (5 failed attempts = 30-minute lockout)
- Session timeout (30 minutes of inactivity)
- Password strength enforcement (minimum 8 characters, alphanumeric)
- Role-based menu access restrictions
- PHP-level access control to prevent URL bypass

### Network Security
- HTTPS enforcement (automatic redirect from HTTP)
- IP address logging for all login attempts

## CSV Import Format

```csv
Employee ID,Name,Department ID,Department Name,Organization Level 1,Organization Level 2,Organization Level 3
EMP001,John Smith,DEPT001,Sales Department,Headquarters,Tokyo Branch,Sales Section
EMP002,Jane Doe,DEPT001,Sales Department,Headquarters,Tokyo Branch,Sales Section
EMP003,Bob Johnson,DEPT002,HR Department,Headquarters,Osaka Branch,HR Section
```

## Database Tables

The plugin creates the following custom tables:

- `{prefix}_mhlw_departments` - Department information and organizational hierarchy
- `{prefix}_mhlw_stress_responses` - Completed stress check responses
- `{prefix}_mhlw_response_details` - Individual question responses (encrypted)
- `{prefix}_mhlw_response_drafts` - Draft/saved responses
- `{prefix}_mhlw_login_attempts` - Login attempt logging for security

## Customization

### Company Logo in PDF
Add a filter to customize the company logo in PDF reports:

```php
add_filter('mhlw_pdf_company_logo', function($logo_url) {
    return 'https://your-company.com/logo.png';
});
```

### Custom Styling
Override CSS in your theme:
- `.mhlw-stress-check-container` - Form container
- `.mhlw-results-container` - Results display
- `.mhlw-dashboard-stats` - Admin dashboard

## Troubleshooting

### "This organization has fewer than 10 valid responses"
This is expected behavior for privacy protection. Group analysis requires at least 10 respondents to prevent individual identification.

### Login Issues
- After 5 failed attempts, account is locked for 30 minutes
- Contact an administrator to reset if needed

### Session Timeout
Users are automatically logged out after 30 minutes of inactivity for security.

## Legal Compliance

This plugin implements the stress check system as defined in:
- Industrial Safety and Health Act (Japan)
- MHLW Stress Check System Implementation Manual

The system includes:
- 57 standardized assessment items
- Proper high-stress identification criteria
- Privacy protection (10-person minimum rule)
- Separation of roles (Industrial Physicians vs General Administrators)
- Confidential data handling

## Support

For technical support or questions about implementation, please contact your system administrator.

## License

GPL-2.0+

## Credits

Developed by Muhammad Ali HAMZA
Compliant with Ministry of Health, Labour and Welfare (MHLW) guidelines
