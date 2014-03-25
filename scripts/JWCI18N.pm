#!/usr/bin/perl -w


package JWCI18N;
use base qw(Locale::Maketext);

use Locale::Maketext 1.01;
use Locale::Maketext::Lexicon 0.10;
use base ('Locale::Maketext::Fuzzy');
use vars qw( %Lexicon );


%Lexicon = (
    '__Content-Type' => 'text/plain; charset=utf-8',

  '_AUTO' => 1,
  # That means that lookup failures can't happen -- if we get as far
  #  as looking for something in this lexicon, and we don't find it,
  #  then automagically set $Lexicon{$key} = $key, before possibly
  #  compiling it.
 
  # The exception is keys that start with "_" -- they aren't auto-makeable.

);
# End of lexicon.



sub Init {
    # Load language-specific functions
    foreach my $language ( glob("po/*.pm")) {
        if ($language =~ /^([-\w.\/\\~:]+)$/) {
            require $1;
        }
        else {
            warn("$language is tainted. not loading");
        }
    }

    # Acquire all .po files and iterate them into lexicons
    Locale::Maketext::Lexicon->import({
        _decode => 1,
	_AUTO   => 1,
        '*'     => [
            Gettext => ("po/*.po")
        ],
    });
    return 1;
}

  
  # And, assuming you want the base class to be an _AUTO lexicon,
  # as is discussed a few sections up:
  
  1;  

