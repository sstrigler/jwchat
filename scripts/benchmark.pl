#!/usr/bin/perl
#
###############################################################################
# Copyright (C) 2003      Florian Bischof <flo@fxb.de>
#                         Stefan Strigler <steve@zeank.in-berlin.de>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
###############################################################################
#
# This script was initally designed to benchmark the wcs component but
# also works fine to test your installation for functionality.
#
# For this script to work you need to have a thread enabled perl
# interpreter.
#
# To setup test users you can use the jabber testsuite which is
# available from http://jabbertest.sourceforge.net/
#
# Here's a short description how it works:
# First it tries to log in -u <users> users for host -h <host>. Then a
# couple of threads is created which begin to talk with each
# other. After that time of simple conversation all users are logged
# off again.
#
###############################################################################

use LWP;
use HTML::Form;
use HTTP::Cookies;
use Time::HiRes qw(time sleep);
use Thread;
use Getopt::Std;

########################################################################
# Configuration
########################################################################

sub usage() {
	print "benchmark.pl -h <host> [-p <wcs port>] -u <users> [-n <users offset>]\n";
	exit 0;
}

getopt('hunp');

usage() unless (defined($opt_h));
usage() unless (defined($opt_u));

my $host = $opt_h;
my $wcs_port = $opt_p || 5280;

my $users = $opt_u;
my $offset = $opt_n || 0;

my $duration = 60;
my $waitfor = 2;
my $forks = 1;
my $pollingtime = 60;		# 12 pro Minute, 720 pro Stunde

my $serverurl = "http://$host:$wcs_port";
#my $serverurl = "http://$host/wcs";
my @actions = (
			{name => 'Login', ratio => 360, urls => ['/logout-sid.js?sid=$sid',
											 '/login-sid.js?jid=$jid&pass=$pass&timeout=10',
											 '/presence.js?sid=$sid&status=online',
											 '/roster.js?sid=$sid&'
											] },
			 #{name => 'Logout', ratio => .1, urls =>  ['/logout-sid.js&sid=$sid'] },
			 {name => 'Message', ratio => 72, urls => ['/message.js?sid=$sid&to=$jidto&type=message&body=$body'] },
			 {name => 'Polling', ratio => 1, urls => ['/cache.js?sid=$sid'] },
			);

my $verbose = 1;

my $pass = "password";
my $jid = "test_\$i\@$host";
my $body = 'Es wäre gut, Bücher zu kaufen, wenn man die Zeit, sie zu lesen, mitkaufen könnte, aber man verwechselt meistens den Ankauf der Bücher mit mit dem Aneignen ihres Inhalts';


########################################################################
# Preparations
########################################################################
my $starttime = time;

# Login of all users:
print STDERR "user login\n";
my @users;
my $ua = new LWP::UserAgent;

for (my $i = $offset; $i < $users+$offset; $i++) {
	my $url = $actions[0]->{urls}[1];
	$url =~ s/\$pass/$pass/;
	$url =~ s/\$jid/$jid/;
	$url =~ s/\$i/$i/;
	
	my $req = HTTP::Request->new(GET => $serverurl.$url);
	# Pass request to the user agent and get a response back
	my $res = $ua->request($req);
	die "Couldn't login user $i ($serverurl.$url)\n" unless ($res->is_success);
	if ($res->content =~ /jabber.sid='([^\']+)'/) {
		$users[$i-$offset] = $1;
	} else {
		die "Couldn't get a sid for user $i\n";
	}
	print STDERR ".";
}

print STDERR "duration: ".(time - $starttime)."\n";

#print STDERR "forking $forks processes...\n";
$starttime = time;
#my @threads;

#for (my $i = 0; $i < $forks; $i++) {
#	$threads[$i] = Thread->new(\&threaded, $i, $starttime);
#}

#print STDERR "processes forked within ".(time - $starttime)."\n";
#my $counter = 0;
#for (my $i = 0; $i < $forks; $i++) {
#	$counter += $threads[$i]->join;
#}

my $counter = threaded(0,$starttime);

$duration = time - ($starttime+$waitfor);

my $reqps = $counter/($duration);
my $userc = $reqps*$pollingtime;

print "$counter requests in total $duration sec. ($reqps per second).\n";
print "--> $userc users @ $pollingtime sec. polling time\n";

print STDERR "logging off users ";

my $user;
foreach $user (@users) {
	my $url = $actions[0]->{urls}[0];
	$url =~ s/\$sid/$user/;

	my $req = HTTP::Request->new(GET => $serverurl.$url);
	# Pass request to the user agent and get a response back
	my $res = $ua->request($req);
	die "Couldn't login user$i ($serverurl$url)\n" unless ($res->is_success); #\'
	print STDERR ".";
}

print STDERR " done.\n";

sub threaded {
	my ($i,$starttime) = @_;
	print STDERR "process # $i\n";
	my $umin = int(($users/$forks)*$i);
	
  # wait and start all threads synchronously
	sleep ($starttime + $waitfor - time);
	print STDERR "$i started\n";
	my %counter;
	$counter{all} = 0;
	$counter{err} = 0;

#	while ((time - ($starttime+$waitfor)) < $duration) {
	while ($counter{all} < 1000) {
		my @urls;
		if (int(rand($actions[0]->{ratio})) == 1) {
			print STDERR "L ";
			@urls = ('/logout-sid.js?sid=$sid',
					 '/login-sid.js?jid=$jid&pass=$pass',
					 '/presence.js?sid=$sid&status=online',
					 '/roster.js?sid=$sid&');
		} elsif (int(rand($actions[1]->{ratio})) == 1) {
			print STDERR "M ";
			@urls = '/message.js?sid=$sid&to=$jidto&type=message&body=$body';
		} else {
			#print STDERR "C ";
			@urls = '/cache.js?sid=$sid';
		}
		
		my $url;
		foreach $url (@urls) {
			my $user = int(rand(int($users/$forks))+$umin);
			# my $user = int(rand($users));
			my $touser = int(rand($users));
			
			$url =~ s/\$pass/$pass/;
			$url =~ s/\$jid/user$jid\@$host/;
			$url =~ s/\$i/$user/;
			if ($users[$user])	{
				$url =~ s/\$sid/$users[$user]/;	
			} else {
				print STDERR "sid for $user undefined!\n";
			}
			$url =~ s/\$jidto/$touser/;
			$url =~ s/\$body/$body/;
			my $req = HTTP::Request->new(GET => $serverurl.$url);
			# Pass request to the user agent and get a response back
			my $res = $ua->request($req);
			if ($res->is_success) {
				if (($url =~ /login-sid/) && ($res->content =~ /jabber.sid='([^\']+)'/)) {
					if (length($1) > 1)	{
						$users[$i] = $1;
					} else {
						die "got invalid sid for $user:\n".$res->content;
					}
				} else {
					#$counter{$actions[2]->{name}}++;		
				}
			} else {
				print STDERR  "\nHTTP-Error ".$res->code." (# $counter{all}, user: test_$user, url: $serverurl$url)\n";
		#          print STDERR $res->as_string."\n";
				$counter{err}++;
				return 0;
			}
			$counter{all}++;
		}
	}

	print STDERR "$counter{all} requests, $counter{err} errors\n";
	return $counter{all};
}
