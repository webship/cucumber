#!/usr/bin/env bash

# Adds the Cucumber testing users of cucumber.users.yml to the site, one user
# for each role, with Drush. No password is kept in the repository: each user
# takes the password in its environment variable, CUCUMBER_<USER>_PASS, or a
# random one made for this run, the way CI makes the passwords of its test
# accounts.

usage() {
  cat <<'HELP'
Usage: bash add-testing-users.sh [--help]

Adds the users of cucumber.users.yml to the Cucumber site, one for each role:
Authenticated user, Developer, Tester, Analyst, Coordinator, Product Owner and
Administrator. Run it from this scripts folder of the Cucumber profile
(web/profiles/contrib/cucumber/scripts); with DDEV, inside the web container:

  ddev exec 'cd web/profiles/contrib/cucumber/scripts && bash add-testing-users.sh'

Passwords: no password is kept in the repository. Each user takes the password
in its environment variable, CUCUMBER_<USER>_PASS, where <USER> is the key of
the user in cucumber.users.yml in capitals:

  CUCUMBER_AUTHENTICATED_PASS   CUCUMBER_DEVELOPER_PASS   CUCUMBER_TESTER_PASS
  CUCUMBER_ANALYST_PASS         CUCUMBER_COORDINATOR_PASS
  CUCUMBER_PRODUCT_OWNER_PASS   CUCUMBER_ADMINISTRATOR_PASS

A user whose variable is not set gets a random password made for this run,
printed once at the end. For example, with DDEV:

  ddev exec 'cd web/profiles/contrib/cucumber/scripts && CUCUMBER_TESTER_PASS="<password>" bash add-testing-users.sh'
HELP
}

if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
  usage
  exit 0
fi

current_path=$(pwd);

# Include the Bash YAML library.
source ${current_path}/libs/bash-yaml.sh || exit 1 ;

# Load the list of default users for Cucumber.
eval $(parse_yaml ${current_path}/cucumber.users.yml);

cd ../../../../;

# Add Drush if it was not in the system.
if [ ! -d "vendor/drush/drush" ]; then
  echo "Add Drush if it was not in the system.";
  composer require "drush/drush:~12.0";
fi

cd web;

made_passwords="";

for user in ${users_[@]}
do
    user_name="user_${!user}_name";
    user_mail="user_${!user}_mail";
    user_role="user_${!user}_role";
    password_variable="CUCUMBER_$(echo "${!user}" | tr '[:lower:]' '[:upper:]')_PASS";
    user_password="${!password_variable}";
    if [ -z "${user_password}" ] ; then
        user_password=$(php -r 'echo bin2hex(random_bytes(12));');
        made_passwords="${made_passwords}  ${!user_name}: ${user_password}\n";
        password_source="made for this run";
    else
        password_source="from ${password_variable}";
    fi

    echo " ---------------------------------------------------------------- ";
    echo "      User name: ${!user_name}";
    echo "      User mail: ${!user_mail}";
    echo "  User password: ${password_source}";
    echo "      User role: ${!user_role}";
    echo " ================================================================= ";

    ../bin/drush user:create "${!user_name}" --mail="${!user_mail}" --password="${user_password}" ;
    if [ "${!user_role}" == '_none_' ] ; then
        echo "   No user role for this user" ;
    else
        ../bin/drush user:role:add "${!user_role}" "${!user_name}" ;
    fi
done

echo "Cache rebuilding ...";
../bin/drush cache:rebuild ;

if [ -n "${made_passwords}" ] ; then
    echo " ---------------------------------------------------------------- ";
    echo " The passwords made for this run (set CUCUMBER_<USER>_PASS to choose them):";
    printf '%b' "${made_passwords}";
fi
