@extends('layouts.guest-content')

@section('title', 'GDPR Compliance - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">GDPR Compliance</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">Last updated: {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="prose dark:prose-invert max-w-none">
        <div class="mb-12">
            <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
                At {{ config('app.name') }}, we are committed to ensuring the security and protection of the personal information
                that we process, and to provide a compliant and consistent approach to data protection. We have always had
                a robust and effective data protection program in place which complies with existing law and abides by the
                data protection principles. However, we recognize our obligations in updating and expanding this program
                to meet the demands of the GDPR.
            </p>
        </div>

        <div class="space-y-8">
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">1. Our Commitment</h2>
                <p>{{ config('app.name') }} is committed to ensuring the security and protection of the personal information that we process, and to provide a compliant and consistent approach to data protection. We are dedicated to safeguarding the personal information under our remit and in developing a data protection regime that is effective, fit for purpose, and demonstrates an understanding of, and appreciation for the new Regulation.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">2. Data Collection & Processing</h2>
                <p>We collect and process personal data only where we have legal bases for doing so under applicable GDPR. We have implemented appropriate technical and organizational measures to ensure compliance with the requirements of the GDPR. This includes:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Data Protection Impact Assessments (DPIA) for high-risk processing</li>
                    <li>Maintaining records of processing activities</li>
                    <li>Implementing data protection by design and by default</li>
                    <li>Ensuring appropriate security measures are in place</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">3. Your Rights Under GDPR</h2>
                <p>As a data subject, you have the following rights under GDPR:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Right to be informed</strong> - about how we process your personal data</li>
                    <li><strong>Right of access</strong> - to the personal data we hold about you</li>
                    <li><strong>Right to rectification</strong> - of inaccurate personal data</li>
                    <li><strong>Right to erasure</strong> - also known as the 'right to be forgotten'</li>
                    <li><strong>Right to restrict processing</strong> - in certain circumstances</li>
                    <li><strong>Right to data portability</strong> - allowing you to obtain and reuse your data</li>
                    <li><strong>Right to object</strong> - to processing in certain circumstances</li>
                    <li><strong>Rights related to automated decision making and profiling</strong></li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">4. Data Security</h2>
                <p>We take the security of all personal data under our control seriously. We implement and maintain appropriate technical and organizational measures to protect personal data against unauthorized or unlawful processing and against accidental loss, destruction, damage, alteration, or disclosure.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">5. Data Breach Notification</h2>
                <p>In the event of a data breach, we have procedures in place to detect, report, and investigate it. Where legally required, we will report certain types of personal data breaches to the relevant supervisory authority within 72 hours of becoming aware of the breach, where feasible.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">6. International Data Transfers</h2>
                <p>Where we transfer personal data outside the European Economic Area (EEA), we ensure appropriate safeguards are in place to protect the data in accordance with GDPR requirements.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">7. Data Protection Officer</h2>
                <p>We have appointed a Data Protection Officer (DPO) to oversee compliance with data protection laws. You can contact our DPO at: <a href="mailto:info@faxt.com" class="text-primary-600 hover:underline dark:text-primary-400">info@faxt.com</a></p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">8. Contact Us</h2>
                <p>If you have any questions about this GDPR Compliance statement or our data protection practices, please contact us at <a href="mailto:privacy@faxt.com" class="text-primary-600 hover:underline dark:text-primary-400">privacy@faxt.com</a>.</p>
            </section>
        </div>
    </div>
</div>
@endsection
