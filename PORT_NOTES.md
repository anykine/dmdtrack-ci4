# dmdtrack CI3 → CI4 port notes

## Ported

| Module | URLs | Files |
|---|---|---|
| Base template, login/logout, languages | `login`, `logout` | `BaseController`, `Filters/AuthFilter`, `Views/themes/default/template.php` |
| Consent | `consent`, `consent/add`, `consent/edit/N` | `Controllers/Consent.php`, `Models/ConsentModel.php`, `Views/consent/*` |
| Contact | `contact/...` | `Contact`, `ContactModel`, `Views/contact/*` |
| Individual (incl. Study menu `?cmd=`) | `individual/...` | `Individual`, `IndividualModel`, `Views/individual/*` |
| Individual detail | `indivDetail/view/N` | `IndivDetail`, `Views/individual/detail.php` |
| Phenotype | `phenotype/...` | `Phenotype`, `PhenotypeModel`, `Views/phenotype/*` |
| Referrer | `referrer/...` | `Referrer`, `ReferrerModel`, `Views/referrer/*` |
| Result | `result/...` | `Result`, `ResultModel`, `Views/result/*` |
| Sample | `sample/...` | `Sample`, `SampleModel`, `Views/sample/*` |
| Sequencing | `sequencing/...` | `Sequencing`, `SequencingModel`, `Views/sequencing/*` |
| User: register, validate, forgot password | `user/register`, `user/validate`, `user/forgot` | `User`, `UsersModel`, `Validation/AccountRules.php`, `Views/user/*` |
| Profile | `profile` | `Profile`, `Views/user/profile_form.php` |

**Not ported yet:** Admin (dashboard, users, settings, contact messages), Welcome (`/` goes to the
Individual list for now), Contactus, Report, Api, Cron, Sitemap.

## Shared code (new in CI4)

The CI3 controllers each repeated the same list/add/edit/delete code. It now lives in:

- `Libraries/ListState.php`: reads sort/dir/limit/page/filters from the URL and only allows known values.
- `Controllers/CrudController.php`: base for the data modules: filter form → GET redirect, "return to the
  list you came from", flash messages, delete with a friendly message when records are still linked.
- `Models/ListModel.php`: `listPage()` runs a list query with whitelisted sort columns and bound filter values.
- `Helpers/list_helper.php`, `Views/partials/list_footer.php`, `Views/partials/delete_modals.php`: list page pieces.
- `Helpers/formgroup_helper.php`: `fg_text()`, `fg_select()`, `fg_radios()`, `fg_select2()`: one call per form field.

Phenotype was ported before these existed and still has its own copy of the list code.

## Behaviour changes from CI3 (on purpose)

**Fixed bugs**
- Delete never worked in Consent, Sample, Sequencing (undefined variable) or Result (deleted from the old
  `results` table). Delete now works in every module, asks for confirmation and uses POST.
- Editing Contact, Individual, Referrer, Sample or Sequencing always showed the "failed" message even when
  it saved (the CI3 update methods returned nothing).
- Result: Platform and Library were never saved (form field names didn't match). Changing a result's
  individual now updates `individual_result_J` too.
- Sequencing edit preselected the sequencing ID in the Sample box instead of the sample.
- Individual detail looked up the referrer by individual ID; it now uses the individual's referrer.
  It also no longer fails for individuals with no samples (CI3 built `IN ()`).
- Individual form: Middle name only showed when the record had a temp ID.
- SQL injection through the `sort`/`dir`/filter URL parameters is fixed in every list.
- All output in views is escaped.

**Other changes**
- Deleting a record that other records still point at (e.g. an individual with samples) shows
  "still linked to other records" instead of a database error.
- Pages use `?page=N` instead of `?offset=N`.
- Required-field stars only show on fields that are actually checked. CI3 showed them everywhere.
- Lists show type names (sample type, test type, consent type) next to or instead of bare IDs.
- On edit, yes/no fields that are empty in the database stay empty. CI3 ticked "Yes" and saved 1.
- **Self-registration is off** (`Config\Site::$allowRegistration`). In CI3 anyone could create an account
  and see every record after confirming their email. Until Admin › Users is ported, add users in the CI3 admin.
- Password reset and new-account codes use a secure random source; the password hash scheme is unchanged,
  so existing passwords keep working.

**Dropped (nothing linked to them):** CSV Export buttons, `add_all`, `view2`, `show_*`,
`view_*_detailAJAX`, `create*`, `update`, `*_AJAX` endpoints, `GetSampleParentSearch`,
`GetsequencingSearch`, the "Family (broken)" field on the Individual form.

## Testing

**Use a copy of the database.** Add/edit/delete change data for real. Then for each module:

- List: sort each column both ways, use each filter box, change items/page, page through
- Add with a required field missing (error shown, values kept), then a full add
- Edit and save: you return to the same sorted/filtered page with "was saved"
- Delete one record; try deleting one that is still linked (friendly message)
- select2 boxes: search and pick; on edit the current value is preselected
- Individual: each Study menu entry; ID link → detail page; all sections fill in
- Profile: change name and language; change password and log in with it
- Forgot password: needs working sendmail on the server
