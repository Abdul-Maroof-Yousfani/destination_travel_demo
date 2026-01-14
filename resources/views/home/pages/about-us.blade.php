@extends('home/layouts/master')

@section('title', 'Home')
@section('style')
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;line-height:1.6;color:#333;}
        .container{max-width:1200px;margin:0 auto;padding:0 20px;}

        /* Hero Section */
        .hero-section{background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);padding:60px 0;}
        .hero-content{display:grid;grid-template-columns:2fr 1fr;gap:40px;align-items:center;margin-bottom:40px;}
        .hero-text h1{font-size:3rem;font-weight:700;margin-bottom:20px;color:#1e293b;}
        .highlight{color:#f59e0b;}
        .hero-text p{font-size:16px;line-height:1.7;color:#64748b;}
        .hero-image img{width:100%;height:auto;border-radius:10px;}
        .customer-support{background:white;border-radius:15px;padding:30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
        .support-info{display:flex;align-items:center;gap:15px;}
        .support-avatar{width:60px;height:60px;border-radius:50%;}
        .support-text h3{font-size:18px;font-weight:600;margin-bottom:5px;}
        .support-text p{color:#64748b;font-size:14px;}
        .support-contact{display:flex;gap:30px;}
        .contact-item{text-align:center;}
        .contact-item span{display:block;font-size:12px;color:#64748b;margin-bottom:5px;}
        .contact-item strong{color:#2563eb;font-size:14px;}
        .contact-item i{color:#30819c;}
        .contact-item a strong{color:#30819c;font-weight:500;}
        /* Mission & Vision */
        .mission-vision{background:#127F9F;color:white;padding:80px 0;}
        .mv-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;}
        .mission h2,.vision h2{font-size:2.5rem;font-weight:700;margin-bottom:20px;}
        .mission p,.vision p{font-size:18px;line-height:1.6;opacity:0.9;}
        /* Values */
        .values-section{padding:80px 0;background:#f8fafc;}
        .values-section h2{text-align:center;font-size:2.5rem;font-weight:700;margin-bottom:50px;color:#1e293b;}
        .values-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:40px;}
        .value-item{text-align:center;padding:30px;background:white;border-radius:15px;box-shadow:0 4px 20px rgba(0,0,0,0.08);}
        .value-icon{font-size:3rem;margin-bottom:20px;}
        .value-item h3{font-size:1.5rem;font-weight:600;margin-bottom:15px;color:#1e293b;}
        .value-item p{color:#64748b;line-height:1.6;}
        /* Journey */
        .journey-section{background:#127F9F;color:white;padding:80px 0;text-align:center;}
        .journey-section h2{font-size:2.5rem;font-weight:700;margin-bottom:40px;}
        .timeline{display:flex;justify-content:center;align-items:center;margin-bottom:30px;gap:20px;}
        .timeline-item{position:relative;}
        .year{background:white;color:#127F9F;padding:10px 20px;border-radius:25px;font-weight:600;font-size:18px;}
        .timeline-item.active .year{background:#f59e0b;color:white;}
        .timeline-line{width:100px;height:2px;background:rgba(255,255,255,0.3);}
        .journey-text{font-size:16px;opacity:0.9;}
        /* Management */
        .management-section{padding:80px 0;background:#f8fafc;}
        .management-section h2{text-align:center;font-size:2.5rem;font-weight:700;margin-bottom:50px;color:#1e293b;}
        .management-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:30px;}
        .team-member{background:white;border-radius:15px;padding:30px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.08);position:relative;}
        .team-member img{width:150px;height:150px;border-radius:50%;margin-bottom:20px;object-fit:cover;}
        .team-member h3{font-size:18px;font-weight:600;margin-bottom:10px;color:#1e293b;}
        .team-member p{color:#64748b;font-size:14px;margin-bottom:15px;}
        .contact-btn{background:#f3f4f6;border:none;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;}
        /* Culture */
        .culture-section{padding:80px 0;}
        .culture-section h2{text-align:center;font-size:2.5rem;font-weight:700;margin-bottom:50px;color:#1e293b;}
        .culture-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
        .culture-gallery img{width:100%;height:250px;object-fit:cover;border-radius:10px;}
        /* Partners */
        .partners-section{padding:80px 0;background:#f8fafc;}
        .partners-section h2{text-align:center;font-size:2.5rem;font-weight:700;margin-bottom:50px;color:#1e293b;}
        .partners-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:30px;align-items:center;}
        .partners-grid img{width:100%;height:60px;object-fit:contain;filter:grayscale(100%);opacity:0.7;transition:all 0.3s ease;}
        .partners-grid img:hover{filter:grayscale(0%);opacity:1;}

        /* Responsive */
        @media (max-width:768px){.hero-content{grid-template-columns:1fr;text-align:center;}
        .mv-grid{grid-template-columns:1fr;gap:40px;}
        .values-grid{grid-template-columns:1fr;}
        .management-grid{grid-template-columns:repeat(2,1fr);}
        .culture-gallery{grid-template-columns:1fr;}
        .partners-grid{grid-template-columns:repeat(3,1fr);}
        .footer-content{grid-template-columns:repeat(2,1fr);}
        .customer-support{flex-direction:column;gap:20px;text-align:center;}
        .support-contact{flex-direction:column;gap:15px;}
        }
        @media (max-width:480px){.management-grid{grid-template-columns:1fr;}
        .partners-grid{grid-template-columns:repeat(2,1fr);}
        .footer-content{grid-template-columns:1fr;}
        .hero-text h1{font-size:2rem;}
        .mission h2,.vision h2,.values-section h2,.journey-section h2,.management-section h2,.culture-section h2,.partners-section h2{font-size:2rem;}
        }
    </style>
@endsection
@section('content')

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>About <span class="highlight">Us</span></h1>
                    <p>travelandtours.pk, based in Karachi, Pakistan, is a leading travel platform dedicated to empowering travelers with instant bookings and a wide range of comprehensive choices. With a focus on delivering exceptional value, travelandtours offers a variety of travel products, including domestic and international flights, hotels, buses, cars, and various services like cutting-edge technology and 24/7 dedicated customer support to enhance the travel experience for our customers. travelandtours operates under the umbrella of THIRD EYE TECHNOLOGY SERVICES PTE LTD, a Singapore-based company specializing in online services for Travel Services and Software Development.</p>
                </div>
                <div class="hero-image">
                    <img src="https://travelandtours.pk/assets/images/" alt="Travel illustration">
                </div>
            </div>
            
            <div class="customer-support">
                <div class="support-info">
                    <img src="https://travelandtours.pk/assets/images/customersupport.webp" alt="Support agent" class="support-avatar">
                    <div class="support-text">
                        <h3>24/7 Customer Support</h3>
                        <p>Expert in travel or general travel support</p>
                    </div>
                </div>
                <div class="support-contact">
                    <div class="contact-item">
                       <div class="icon-head"><i class="fa-solid fa-phone"></i></div>
                        <a href="tel:92 01234567 0"><strong>(021) 32460260–61</strong></a>
                    </div>
                    <div class="contact-item">
                        <div class="icon-head"><i class="fa-brands fa-whatsapp"></i></div>
                       <a  href="tel:92 01234567 0"> <strong>+923123456789</strong></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-vision">
        <div class="container">
            <div class="mv-grid">
                <div class="mission">
                    <h2>Our <span class="highlight">Mission</span></h2>
                    <p>To empower our customers to travel, experience, and connect with the world.</p>
                </div>
                <div class="vision">
                    <h2>Our <span class="highlight">Vision</span></h2>
                    <p>To be the go-to travel company for those looking for unique and authentic experiences.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="values-section">
        <div class="container">
            <h2>Our <span class="highlight">Values</span></h2>
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-icon">🎯</div>
                    <h3>Curiosity</h3>
                    <p>Display an innate curiosity to explore new places, learn new skills and meet all categories of people, encouraging conversations which deepen understanding of a variety of subjects.</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">🤝</div>
                    <h3>Humility</h3>
                    <p>Be humble when talking about your achievements, never be too proud to ask smart questions or gaps in knowledge. Constantly be learning and discovering that you can not know everything.</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">⚡</div>
                    <h3>Simplicity</h3>
                    <p>Simple solutions are the most beautiful ones. Customers and internal stakeholders value simplicity of execution, experiences and solutions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Journey -->
    <section class="journey-section">
        <div class="container">
            <h2>Our <span class="highlight">Journey</span></h2>
            <div class="timeline">
                <div class="timeline-item ">
                    <div class="year">2016</div>
                </div>
                <div class="timeline-line"></div>
                <div class="timeline-item">
                    <div class="year">2018</div>
                </div>
                <div class="timeline-line"></div>
                <div class="timeline-item">
                    <div class="year">2021</div>
                </div>
                 <div class="timeline-line"></div>
                <div class="timeline-item active">
                    <div class="year">2025</div>
                </div>
            </div>
            <p class="journey-text">travelandtours.pk is launched with a mission to transform travels.</p>
        </div>
    </section>

    <!-- Management -->
    <section class="management-section">
        <div class="container">
            <h2>Our <span class="highlight">Management</span></h2>
            <div class="management-grid">
                <div class="team-member">
                    <img src="https://travelandtours.pk/assets/images/" alt="Anonymous 1">
                    <h3>Anonymous q</h3>
                    <p>Anonymous q</p>
                    <button class="contact-btn">📧</button>
                </div>
                <div class="team-member">
                    <img src="https://travelandtours.pk/assets/images/" alt="Anonymous 2">
                    <h3>Anonymous 2</h3>
                    <p>Anonymous 2</p>
                    <button class="contact-btn">📧</button>
                </div>
                <div class="team-member">
                    <img src="https://travelandtours.pk/assets/images/" alt="Anonymous 3">
                    <h3>Anonymous 3</h3>
                    <p> Anonymous 3</p>
                    <button class="contact-btn">📧</button>
                </div>
                <div class="team-member">
                    <img src="https://travelandtours.pk/assets/images/" alt=" Anonymous 4">
                    <h3>Anonymous 4</h3>
                    <p>Anonymous 4</p>
                    <button class="contact-btn">📧</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Culture -->
    <section class="culture-section">
        <div class="container">
            <h2>Our <span class="highlight">Culture</span></h2>
            <div class="culture-gallery">
                <img src="https://travelandtours.pk/assets/images/" alt="Office culture 1">
                <img src="https://travelandtours.pk/assets/images/" alt="Office culture 1">
                <img src="https://travelandtours.pk/assets/images/" alt="Office culture 2">
                <img src="https://travelandtours.pk/assets/images/" alt="Office culture 3">
            </div>
        </div>
    </section>

    <!-- Corporate Partners -->
    <section class="partners-section">
        <div class="container">
            <h2>Our <span class="highlight">Corporate Partners</span></h2>
            <div class="partners-grid">
                <img src="https://travelandtours.pk/assets/images/" alt="Partners 1">
                <img src="https://travelandtours.pk/assets/images/" alt="Partners 2">
                <img src="https://travelandtours.pk/assets/images/" alt="Partners 3">
                <img src="https://travelandtours.pk/assets/images/" alt="Partners 4">
                <img src="https://travelandtours.pk/assets/images/" alt="Partners 5">
            </div>
        </div>
    </section>
@endsection
@section('script')
@endsection