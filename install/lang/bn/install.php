<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Automatically generated strings for Moodle installer
 *
 * Do not edit this file manually! It contains just a subset of strings
 * needed during the very first steps of installation. This file was
 * generated automatically by export-installer.php (which is part of AMOS
 * {@link http://docs.moodle.org/dev/Languages/AMOS}) using the
 * list of strings defined in /install/stringnames.txt.
 *
 * @package   installer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['admindirname'] = 'অ্যাডমিন ডিরেক্টরিটি';
$string['availablelangs'] = 'প্রাপ্তিসাধ্য ভাষার তালিকা';
$string['chooselanguagehead'] = 'একটি ভাষা নির্বাচন';
$string['chooselanguagesub'] = 'ইনস্টলেশনের জন্য একটি ভাষা নির্বাচন। এই ভাষাই সাইটের জন্য নির্ধারিত ভাষা হিসাবে ব্যবহৃত হবে এবং এটা পরে যেকোনো সময় পরিবর্তন করা হতে পারে।';
$string['clialreadyinstalled'] = 'ফাইল config.php ইতিমধ্যে আছে, অনুগ্রহ করে আপনি আপনার সাইটটি আপগ্রেড করতে চাইলে admin/cli/upgrade.php ব্যবহার।';
$string['cliinstallheader'] = 'মুডল {$a} কমান্ড লাইন ইনস্টলেশন প্রোগ্রাম';
$string['databasehost'] = 'ডাটাবেস হোস্ট';
$string['databasename'] = 'ডাটাবেস নাম';
$string['databasetypehead'] = 'ডাটাবেস ড্রাইভার নির্বাচন';
$string['dataroot'] = 'ডাটাবেস ডিরেক্টরি';
$string['dbprefix'] = 'টেবিল প্রেফিক্স';
$string['dirroot'] = 'মুডল ডিরেক্টরি';
$string['environmenthead'] = 'এনভায়রনমেন্ট পরীক্ষা করা হচ্ছে ...';
$string['environmentsub2'] = 'প্রত্যেক মুডল রিলিজের  PHP সংস্করণের কিছু নূন্যতম শর্ত এবং কিছু অত্যাবশ্যক PHP এক্সটেনশন রয়েছে।
ইনস্টল ও আপগ্রেডের পূর্বেই সম্পূর্ন এনভায়রনমেন্ট পরীক্ষা করা হয়েছে। নতুন সংস্করনটা ইনস্টল করতে বা PHP এক্সটেনশন সক্রিয় করতে না পারলে অনুগ্রহ করে সার্ভার প্রশাসন এর সাথে যোগাযোগ।';
$string['errorsinenvironment'] = 'এনভায়রনমেন্ট পরীক্ষা ব্যর্থ!';
$string['installation'] = 'ইনস্টলেশন';
$string['langdownloaderror'] = 'দূর্ভাগ্যবশত "{$a}" ভাষা  ডাউনলোড করা যাচ্ছে না। ইনস্টলেশন প্রক্রিয়া ইংরেজী ভাষাতেই চলবে।';
$string['memorylimithelp'] = '<p>আপনার সার্ভারের PHP মেমরি সীমা বর্তমানে {$a} এ নির্ধারণ করা হয়েছে।</p>

<p>ফলে মুডলে পরবর্তীতে মেমরি সংক্রান্ত সমস্যা দেখা দিতে পারে, বিশেষ করে অনেকগুলো মুডল সক্রিয় করা থাকলে/বেশিসংখ্যক ব্যবহারকারী থাকলে। </p>

<p>এজন্য আমরা সুপারিশ করি উচ্চসীমার, যেমন ৪০M মেমরি সহ PHP কমপাইল করতে।
   এটা করার অনেকগুলো উপায় রয়েছে:</p>
<ol>
<li>আপনি যদি <i>--enable-memory-limit</i> দ্বারা PHP কমপাইল করতে পারেন তবে।
    ফলে মুডল নিজে মেমরি সীমা নির্ধারণ করে নিবে।</li>
<li>আপনি php.ini ফাইল ব্যবহার করতে পারলে <b>memory_limit</b>
     পরিবর্তন করে ৪০M এর মত করা যেত। আপনি যদি নিজে ব্যবহার করতে না পারেন তবে আপনার অ্যাডমিনিস্ট্রেশন আপনার জন্য এ কাজ করে দিবে।</li>
<li>কিছু PHP সার্ভারে মুডল ডিরেক্টরিতে:
    <blockquote><div>php_value memory_limit 40M</div></blockquote>
  লাইনটিসহ .htaccess ফাইল তৈরি করে নিতে পারেন
    <p>কিছু সার্ভারে <b>সকল</b> PHP পৃষ্ঠা কাজ নাও করতে পারে
    (পৃষ্ঠায় আপনার কিছু ত্রুটি চোখে পড়বে) সুতরাং আপনাকে .htaccess ফাইল অপসারন করতে হবে।</p></li>
</ol>';
$string['paths'] = 'পাথ';
$string['pathserrcreatedataroot'] = 'ইনস্টলারের সাহায্যে ডাটা ডিরেক্টরি ({$a->dataroot}) তৈরি করা যায় না';
$string['pathshead'] = 'পাথ নিশ্চিত';
$string['pathsrodataroot'] = 'ডাটারুট ডিরেক্টরি লেখার মত নয়।';
$string['pathsroparentdataroot'] = 'প্যারেন্ট ডিরেক্টরি ({$a->parent}) লেখার যোগ্য নয়। ইনস্টলারের সাহায্যে ডাটা ডিরেক্টরি ({$a->dataroot}) তৈরি করা যায় না।';
$string['pathssubadmindir'] = 'খুব কম সংখ্যক ওয়েবহোস্ট কন্ট্রোল প্যানেল বা এই ধরনের কিছু ব্যবহার করতে /admin কে বিশেষ URL হিসাবে ব্যবহার করে। দূর্ভাগ্যবশত এর সাথে মুডল এডমিন পৃষ্টার স্ট্যান্ডার্ড স্থান নিয়ে ঝামেলা হয়। ইনস্টলেশনে অ্যাডমিন ডিরেক্টরির
নাম পরিবর্তন করে নতুন নাম দিয়ে এ সমস্যার সমাধান করতে পারেন। যেমন: <em>moodleadmin</em>। এর ফলে মডুলে অ্যাডমিনে লিঙ্কগুলো নির্ধারণ করে দিবে।';
$string['pathssubdataroot'] = 'মুডলের ফাইল সংরক্ষণের জন্য জায়গা প্রয়োজন। ওয়েব সার্ভার ব্যবহারকারী
(সাধরনত কেউই না বা apache) যাতে অবশ্যই এই ডিরেক্টরি পড়তে ও লিখতে পারে, কিন্তু যাতে ওয়েবের সাহায্যে এটা সরাসরি ব্যবহার করা না যায়। ডিরেক্টরি না থাকলে, ইনস্টলার এটা তৈরি করে নেয়ার চেষ্টা করবে।';
$string['pathssubdirroot'] = 'মুডল ইন্সটলেশনের সম্পূর্ন ডিরেক্টরি পাথ।';
$string['pathssubwwwroot'] = 'মুডল ব্যবহার করার সম্পূর্ন ওয়েব ঠিকানা।
একাধিক ঠিকানা ব্যবহার করে মুডল ব্যবহার করা সম্ভব নয়।
আপনার সাইটের যদি একাধিক পাবলিক ঠিকানা থাকে তবে এই ঠিকানাটা ছাড়া বাকি সবগুলো ঠিকানার জন্য আপনাকে অবশ্যই স্থায়ী রিডিরেক্ট নির্ধারণ করতে হবে।';
$string['pathsunsecuredataroot'] = 'ডাটারুট এর স্থান নিরাপদ নয়';
$string['pathswrongadmindir'] = 'অ্যাডমিন ডিরেক্টরি নাই';
$string['phpextension'] = '{$a} PHP এক্সটেনশন';
$string['phpversion'] = 'PHP সংস্করণ';
$string['phpversionhelp'] = '<p>মুডলের জন্য PHP এর কমপক্ষে 4.3.0 or 5.1.0সংস্করণ প্রয়োজন (5.0.x এ অবগত সমস্যার সংখ্যাগতমান দেয়া থাকে)।</p>
<p>আপনি বর্তমানে {$a} সংস্করণটি চালাচ্ছেন</p>
<p>PHP সংস্করন অবশ্যই আপগ্রেড করতে হবে বা PHP এর নতুন সংস্করনসহ হোস্ট ব্যবহার করতে হবে!<br />
(5.0.x এর ক্ষেত্রে আপনি 4.4.x সংস্করনেও ডাউনগ্রেড করতে পারেন)</p>';
$string['welcomep10'] = '{$a->installername} ({$a->installerversion})';
$string['welcomep20'] = 'আপনি সফলভাবে
     আপনার কম্পিউটারে <strong>{$a->packname} {$a->packversion}</strong> প্যাকেজ ইনস্টল করে চালু করেছেন বলেই এই পৃষ্ঠাটি দেখতে পাচ্ছেন। অভিনন্দন!';
$string['welcomep30'] = 'অ্যাপ্লিকেশনসহ <strong>{$a->installername}</strong> রিলিজের ফলে একটি এনভায়রনমেন্ট তৈরি করা হয় যেখানে <strong>মুডল</strong> কাজ করবে, যেমন:';
$string['welcomep40'] = 'প্যাকেজে <strong>মুডল {$a->moodlerelease} ({$a->moodleversion})</strong> ও থাকে।';
$string['welcomep50'] = 'এই প্যাকেজের সকল অ্যাপ্লিকেশন তাদের মূল্যবান
    লাইসেন্স অনুসরন করে। সম্পূর্ন <strong>{$a->installername}</strong> প্যাকেজ <a href="http://www.opensource.org/docs/definition_plain.html">ওপেন সোর্স</a> এবং <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a> লাইসেন্সের আওতায়
    বন্টিত।';
$string['welcomep60'] = 'নিম্নোক্ত পৃষ্ঠায় আপনার কম্পিউটারে <strong>মুডল</strong>
     কনফিগার করে নির্ধারন করার জন্য কিছু সহজ উপায় দেয়া আছে। আপনি পূর্বনির্ধারিত
    সেটিং গ্রহন করতে পারেন অথবা ঐচ্ছিকভাবে তাদেরকে আপনার প্রয়োজনানুসারে সংশোধন করে নিতে পারেন।';
$string['welcomep70'] = '<strong>মুডল</strong> সেট আপের সাহায্যে অগ্রসর হওয়ার জন্য "পরবর্তী" বোতামে ক্লিক।';
$string['wwwroot'] = 'ওয়েব ঠিকানা';
