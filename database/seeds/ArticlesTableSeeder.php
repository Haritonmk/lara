<?php

use Illuminate\Database\Seeder;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('articles')->insert([
            ['id' => '1', 'name_rus' => '', 'name_eng' => 'Air Canada flight finds stranded Australian yacht', 'body_eng' => '<p>They were ready to land in Australia, at the end of a 14-hour international flight, when the 270 passengers of an Air Canada flight were suddenly thrown into a high-seas search-and-rescue operation.</p>

<p>Flight AC033 diverted after pilot Andrew Robertson got a call from the Australian Maritime Safety Authority on Tuesday to help search for a yachtsman who had sailed from Sydney two weeks earlier.</p>

<p>"If we have the fuel, could we investigate an emergency beacon that had just gone off," came the question from maritime officials, Robertson told CNN Canadian affiliate CBC News.</p>

<p>Down below, Glenn Ey of Queensland, Australia, was being tossed about in his crippled 36-foot yacht -- out of fuel and with a broken mast after a storm.
"I thought I had a very good chance of getting back to Sydney without assistance," Ey said after nine days adrift. "I couldn&rsquo;t see any evidence of Sydney, and I had no idea of my exact position, and it was at that point I set off the emergency position indicator radio beacon."</p>

<p>The search began as the Boeing 777, on its way from Vancouver, dropped from 37,000 feet to 4,000 feet. Robertson asked the passengers and crew to train their eyes on the choppy waters below.</p>

<p>"I think everyone&rsquo;s heart started beating a little bit faster," said Jill Barber, a Canadian singer, who was making the trip to Sydney for a concert. "They said ... we&rsquo;d really appreciate it if everyone could look out their windows, and if anyone has any binoculars that could help us identify this yacht, that would be really helpful."</p>

<p>It didn&rsquo;t take too long to find Ey as passengers and crew scanned the waters below.</p>

<p>"We&rsquo;re doing this big sweeping right turn and almost immediately they said, &rsquo;Oh, we see something,&rsquo; " Robertson said. "We were totally ecstatic."
Total from time from activation of the emergency beacon until he was found by the Air Canada flight: about 25 minutes.</p>

<p>"You know, we cheered and we applauded and I think we all kind of felt a sense of pride," Barber said.</p>

<p>A merchant vessel helped the yacht until the New South Wales water police arrived from Sydney late Wednesday, about 270 nautical miles off the coast.</p>']
        ]);
    }
}
