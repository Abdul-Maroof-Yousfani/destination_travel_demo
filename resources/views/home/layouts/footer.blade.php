<footer>
    <div class="container">
       <div class="row">
          <div class="col-md-12 col-lg-2">
             <div class="widget">
                <h5>Support</h5>
                <ul>
                   <li>
                      <a href="#">Account</a>
                   </li>
                   <li>
                      <a href="#">Faq</a>
                   </li>
                   <li>
                      <a href="#">Legal</a>
                   </li>
                   <li>
                      <a href="#">Contact</a>
                   </li>
                   <li>
                      <a href="#">Affiliate Program</a>
                   </li>
                   <li>
                      <a href="{{ route('terms-and-conditions') }}">Terms And Conditions</a>
                   </li>
                </ul>
             </div>
          </div>
          <div class="col-md-12 col-lg-2">
             <div class="widget">
                <h5>Company</h5>
                <ul>
                   <li>
                      <a href="{{ route('about-us') }}">About Us</a>
                   </li>
                   <li>
                      <a href="#">Testimonials</a>
                   </li>
                   <li>
                      <a href="#">Rewards</a>
                   </li>
                   <li>
                      <a href="#">Work with Us</a>
                   </li>
                   <li>
                      <a href="#">Meet the Team</a>
                   </li>
                   <li>
                      <a href="#">Blog</a>
                   </li>
                </ul>
             </div>
          </div>
          <div class="col-md-12 col-lg-2">
             <div class="widget">
                <h5>Other Services</h5>
                <ul>
                   <li>
                      <a href="#">Community program</a>
                   </li>
                   <li>
                      <a href="#">Investor Relations</a>
                   </li>
                   <li>
                      <a href="#">Rewards Program</a>
                   </li>
                   <li>
                      <a href="#">Points PLUS</a>
                   </li>
                   <li>
                      <a href="#">Partners</a>
                   </li>
                   <li>
                      <a href="#">List My Hotel</a>
                   </li>
                </ul>
             </div>
          </div>
          <div class="col-md-12 col-lg-2">
             <div class="widget">
                <h5>Top Cities</h5>
                <ul>
                   <li>
                      <a href="#">Chicago</a>
                   </li>
                   <li>
                      <a href="#">New York</a>
                   </li>
                   <li>
                      <a href="#">San Francisco</a>
                   </li>
                   <li>
                      <a href="#">California</a>
                   </li>
                   <li>
                      <a href="#">Ohio</a>
                   </li>
                   <li>
                      <a href="#">Alaska</a>
                   </li>
                </ul>
             </div>
          </div>
          <div class="col-md-12 col-lg-4">
             <div class="widget">
                <h5>Need any help?</h5>
                <p><i class="fa-solid fa-phone"></i> Call  24/7 for any help</p>
                <br>
                <a href="tel:{{ config('variables.contact.phone') }}"><h5>{{ config('variables.contact.phone') }}</h5></a>
                <p><i class="fa-solid fa-envelope"></i> Mail to our support team</p>
                <br>
                <a href="mailto:support@travelandtour.com"><h4>support@travelandtour.com</h4></a>
             </div>
          </div>
       </div>
    </div>
 </footer>
 <div class="copyright">
    
       <div class="container">
          <div class="row">
             <div class="col-md-12 col-lg-8 text-left copadd">
                <div class="copy">
                   <p>Address: {{ config('variables.contact.address') }}</p>
                   {{-- <p>Copyright © 1996–2024 Travel & Tour. All rights reserved.</p> --}}
                </div>
             </div>
             <div class="col-md-12 col-lg-4 text-left copadd">
                <div class="copy2">
                   <p>Follow us on</p>
                   <div class="socal">
                      <ul>
                         <li>
                            <a href="#"><img src="/assets/images/fb.png" alt=""></a>
                         </li>
                         <li>
                            <a href="#"><img src="/assets/images/ing.png" alt=""></a>
                         </li>
                         <li>
                            <a href="#"><img src="/assets/images/x.png" alt=""></a>
                         </li>
                         <li>
                            <a href="#"><img src="/assets/images/lin.png" alt=""></a>
                         </li>
                      </ul>
                   </div>
                </div>
             </div>
          </div>
       </div>
 </div>
 