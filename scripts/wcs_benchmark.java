import java.io.*;
import java.net.*;

import java.util.Date;
import java.util.regex.*;

public class wcs_benchmark {
    static String hostname;
    static int wcs_port = 5280;
    static int users;
    static int offset = 0;
    
    static HttpURLConnection httpcon;
    static String sids[]; // he we store the sids from logged in users

    /* default values as they are used in jabber testsuite */
    static String user_prefix = "test_";
    static String pass = "password";


    static int num_threads = 16;
    static Thread threads[];

    static String default_message = "HalloWelt";

    public static void usage() {
        System.out.println("java wcs_benchmark <host>:[port] <users> [offset]");
        System.exit(0);
    }

    public static String getURL(String url) {
        try {
            httpcon = (HttpURLConnection)(new URL("http",hostname,wcs_port,url)).openConnection();
            //                System.out.println(httpcon.getResponseMessage());
            BufferedReader br = new BufferedReader(new InputStreamReader(httpcon.getInputStream()));
            
            String content;
            String retval = "";
            while ((content = br.readLine()) != null) { retval += content; }
            
            br.close();
            return retval;
        } catch (IOException e) {
            System.err.println(e.toString());
            return null;
        }
    }

    public static void main(String args[]) {
        /* do some command line parsing */

        if (args.length < 2 || args.length > 3)
            usage();

        if (args[0].indexOf(':') != -1) {
            hostname = args[0].substring(0,args[0].indexOf(':'));
            wcs_port = (new Integer(args[0].substring(args[0].indexOf(':')+1))).intValue();
        } else 
            hostname = args[0];

        users = (new Integer(args[1])).intValue();
        sids = new String[users];
        
        if (args.length == 3)
            offset = new Integer(args[2]).intValue();

        threads = new Thread[num_threads];

        /*
         * log in users 
         */

        System.out.print("Logging in "+ users+" users ");
        Date starttime = new Date();

        Pattern sid = Pattern.compile(".*jabber.sid='(.*)';.*");

        for (int i=offset; i<users+offset; i++) {
            String url = "/login-sid.js?jid="+user_prefix+i+"@"+hostname+"&pass="+pass;

            Matcher m = sid.matcher(getURL(url));
            if (m.matches())
                sids[i-offset] = m.group(1);
            else {
                System.err.println("Couldn't get sid for user test_"+i);
                System.exit(1);
            }

            /* send presence */
            getURL("/presence.js?sid="+sids[i-offset]+"&status=available");

            /* get roster */
            getURL("/roster.js?sid="+sids[i-offset]);

            System.out.print(".");
        }

        Date endtime = new Date();

        System.out.println(" done (" + (endtime.getTime()-starttime.getTime())/1000.0 + "sec.)");

//         for (int i=0;i<sids.length;i++)
//             System.out.println(sids[i]);

        /*
         * created workers and let them work
         */
//         for (int i=0; i<threads.length; i++)
//             threads[i] = new Thread(new BenchmarkWorker(i));
//         for (int i=0; i<threads.length; i++)
//             threads[i].start();
//         for (int i=0; i<threads.length; i++)
//             threads[i].join();


        int request_counter = 0;

        java.util.Random rand = new java.util.Random();
        starttime = new Date();
        int num_requests = 10000;
        while (request_counter++ < num_requests) {
            String urls[];
            if (request_counter%10==0) {
                urls = new String[4];
                urls[0] = "/logout-sid.js?sid=";
                urls[1] = "/login-sid.js";
                urls[2] = "/presence.js?sid=";
                urls[3] = "/roster.js?sid=";
                System.out.print(" L");
            } else if (request_counter%3==0) {
                urls = new String[1];
                urls[0] = "/message.js?body="+default_message+"&sid=";
                System.out.print(" M");
            } else {
                urls = new String[1];
                urls[0] = "/cache.js?sid=";
            }

            // choose user
            int user = rand.nextInt(users);
            //            System.out.print(" "+user);

            for (int i=0; i<urls.length; i++) {
                String url;
                if (urls[i].indexOf("login-sid") != -1) {
                    url = urls[i] + "?jid="+user_prefix+user+"@"+hostname+"&pass="+pass;
                    Matcher m = sid.matcher(getURL(url));
                    if (m.matches())
                        sids[i] = m.group(1);
                    else {
                        System.err.println("Couldn't get sid for user test_"+i);
                        System.exit(1);
                    }
                } else {
                    url = urls[i] + sids[user];
                    getURL(url);
                }
            }
        }
        
        endtime = new Date();
        double duration = (endtime.getTime()-starttime.getTime())/1000.0;

        System.out.println(" 1000 requests served in " + duration  + "sec. ("+num_requests/duration+"/sec)");

        /*
         * log out users 
         */

        System.out.print("Logging out users ");
        for (int i=0; i<users; i++) {
            String url = "/logout-sid.js?sid="+sids[i];
            getURL(url);
            System.out.print('.');
        }
        System.out.println(" done.");
    }
}
