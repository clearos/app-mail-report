
Name: app-mail-report
Epoch: 1
Version: 1.6.5
Release: 1%{dist}
Summary: Mail Report
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
The Mail Report provides useful information on the state of mail flowing through your system.

%package core
Summary: Mail Report - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-reports-core
Requires: app-tasks-core
Requires: postfix-perl-scripts

%description core
The Mail Report provides useful information on the state of mail flowing through your system.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/mail_report
cp -r * %{buildroot}/usr/clearos/apps/mail_report/

install -D -m 0644 packaging/app-mail-report.cron %{buildroot}/etc/cron.d/app-mail-report

%post
logger -p local6.notice -t installer 'app-mail-report - installing'

%post core
logger -p local6.notice -t installer 'app-mail-report-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/mail_report/deploy/install ] && /usr/clearos/apps/mail_report/deploy/install
fi

[ -x /usr/clearos/apps/mail_report/deploy/upgrade ] && /usr/clearos/apps/mail_report/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-mail-report - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-mail-report-core - uninstalling'
    [ -x /usr/clearos/apps/mail_report/deploy/uninstall ] && /usr/clearos/apps/mail_report/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/mail_report/controllers
/usr/clearos/apps/mail_report/htdocs
/usr/clearos/apps/mail_report/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/mail_report/packaging
%dir /usr/clearos/apps/mail_report
/usr/clearos/apps/mail_report/deploy
/usr/clearos/apps/mail_report/language
/usr/clearos/apps/mail_report/libraries
/etc/cron.d/app-mail-report
