---
sidebarDepth: 2
---

# Roles & Permissions

## Setting things up

Let's start by creating the following `Role`s:

```php
$owner = Role::create([
    'name' => 'owner',
    'display_name' => 'Project Owner', // optional
    'description' => 'User is the owner of a given project', // optional
]);

$admin = Role::create([
    'name' => 'admin',
    'display_name' => 'User Administrator', // optional
    'description' => 'User is allowed to manage and edit other users', // optional
]);
```

Now we need to add `Permission`s:

```php
$createPost = Permission::create([
    'name' => 'create-post',
    'display_name' => 'Create Posts', // optional
    'description' => 'create new blog posts', // optional
]);

$editUser = Permission::create([
    'name' => 'edit-user',
    'display_name' => 'Edit Users', // optional
    'description' => 'edit existing users', // optional
]);
```

## Role Permissions Assignment & Removal

### Assignment

```php
$admin->givePermission($createPost); // parameter can be a Permission object, array or id
// equivalent to $admin->permissions()->attach([$createPost->id]);

$owner->givePermissions([$createPost, $editUser]); // parameter can be a Permission object, array or id
// equivalent to $owner->permissions()->attach([$createPost->id, $editUser->id]);

$owner->syncPermissions([$createPost, $editUser]); // parameter can be a Permission object, array or id
// equivalent to $owner->permissions()->sync([$createPost->id, $editUser->id]);
```

### Removal

```php
$admin->removePermission($createPost); // parameter can be a Permission object, array or id
// equivalent to $admin->permissions()->detach([$createPost->id]);

$owner->removePermissions([$createPost, $editUser]); // parameter can be a Permission object, array or id
// equivalent to $owner->permissions()->detach([$createPost->id, $editUser->id]);
```

## User Roles Assignment & Removal

With both roles created let's assign them to the users.

### Assignment

```php
$user->addRole($admin); // parameter can be a Role object, array, id or the role string name
// equivalent to $user->roles()->attach([$admin->id]);

$user->addRoles([$admin, $owner]); // parameter can be a Role object, array, id or the role string name
// equivalent to $user->roles()->attach([$admin->id, $owner->id]);

$user->syncRoles([$admin->id, $owner->id]);
// equivalent to $user->roles()->sync([$admin->id, $owner->id]);

$user->syncRolesWithoutDetaching([$admin->id, $owner->id]);
// equivalent to $user->roles()->syncWithoutDetaching([$admin->id, $owner->id]);
```

### Removal

```php
$user->removeRole($admin); // parameter can be a Role object, array, id or the role string name
// equivalent to $user->roles()->detach([$admin->id]);

$user->removeRoles([$admin, $owner]); // parameter can be a Role object, array, id or the role string name
// equivalent to $user->roles()->detach([$admin->id, $owner->id]);
```

## User Permissions Assignment & Removal

You can give single permissions to a user, so in order to do it you only have to make:

### Assignment

```php
$user->givePermission($editUser); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->permissions()->attach([$editUser->id]);

$user->givePermissions([$editUser, $createPost]); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->permissions()->attach([$editUser->id, $createPost->id]);

$user->syncPermissions([$editUser->id, $createPost->id]);
// equivalent to $user->permissions()->sync([$editUser->id, createPost->id]);

$user->syncPermissionsWithoutDetaching([$editUser, $createPost]); // parameter can be a Permission object, array or id
    // equivalent to $user->permissions()->syncWithoutDetaching([$createPost->id, $editUser->id]);
```

### Removal

```php
$user->removePermission($createPost); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->permissions()->detach([$createPost->id]);

$user->removePermissions([$createPost, $editUser]); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->permissions()->detach([$createPost->id, $editUser->id]);
```

## Checking for Roles & Permissions

Now we can check for roles and permissions simply by doing:

```php
$user->hasRole('owner');   // false
$user->hasRole('admin');   // true
$user->hasPermission('edit-user');   // false
$user->isAbleTo('edit-user');   // false
$user->hasPermission('create-post'); // true
$user->isAbleTo('create-post'); // true
```

::: tip NOTE
If you want, you can use the `hasPermission` or `isAbleTo`.
:::

::: tip NOTE
We dropped the usage of the `can` method in order to have full support to Laravel's Gates and Policies.
:::

Both `hasPermission()` and `hasRole()` can receive an array or pipe separated string of roles & permissions to check:

```php
$user->hasRole(['owner', 'admin']);       // true
$user->hasPermission(['edit-user', 'create-post']); // true

$user->hasRole('owner|admin');       // true
$user->hasPermission('edit-user|create-post'); // true
```

By default, if any of the roles or permissions are present for a user then the method will return true.
Passing `true` as a second parameter instructs the method to require **all** of the items:

```php
$user->hasRole(['owner', 'admin']);             // true
$user->hasRole(['owner', 'admin'], true);       // false, user does not have admin role
$user->hasPermission(['edit-user', 'create-post']);       // true
$user->hasPermission(['edit-user', 'create-post'], true); // false, user does not have edit-user permission
```

You can have as many `Role`s as you want for each `User` and vice versa. Also, you can have as many direct `Permissions`s as you want for each `User` and vice versa.

The `Laratrust` class has shortcuts to both `hasPermission()` and `hasRole()` for the currently logged in user:

```php
Laratrust::hasRole('role-name');
Laratrust::hasPermission('permission-name');

// is identical to

Auth::user()->hasRole('role-name');
Auth::user()->hasPermission('permission-name');
```

You can also use wildcard to check any matching permission by doing:

```php
// match any admin permission
$user->hasPermission('admin.*'); // true

// match any permission about users
$user->hasPermission('*-users'); // true
```

## User ability

More advanced checking can be done using the awesome `ability` function.
It takes in three parameters (roles, permissions, options):

- `roles` is a set of roles to check.
- `permissions` is a set of permissions to check.
- `options` is a set of options to change the method behavior.

Either of the roles or permissions variable can be a pipe separated string or an array:

```php
$user->ability(['admin', 'owner'], ['create-post', 'edit-user']);

// or

$user->ability('admin|owner', 'create-post|edit-user');
```

This will check whether the user has any of the provided roles and permissions.
In this case it will return true since the user is an `admin` and has the `create-post` permission.

The third parameter is an options array:

```php
$options = [
    'validate_all' => true, //Default: false
    'return_type'  => 'array' //Default: 'boolean'. You can also set it as 'both'
];
```

- `validate_all` is a boolean flag to set whether to check all the values for true, or to return true if at least one role or permission is matched.
- `return_type` specifies whether to return a boolean, array of checked values, or both in an array.

Here is an example output:

```php
$options = [
    'validate_all' => true,
    'return_type' => 'both'
];

[$validate, $allValidations] = $user->ability(
    ['admin', 'owner'],
    ['create-post', 'edit-user'],
    $options
);

var_dump($validate);
// bool(false)

var_dump($allValidations);
// array(4) {
//     ['role'] => bool(true)
//     ['role_2'] => bool(false)
//     ['create-post'] => bool(true)
//     ['edit-user'] => bool(false)
// }
```

The `Laratrust` class has a shortcut to `ability()` for the currently logged in user:

```php
Laratrust::ability('admin|owner', 'create-post|edit-user');

// is identical to

Auth::user()->ability('admin|owner', 'create-post|edit-user');
```
