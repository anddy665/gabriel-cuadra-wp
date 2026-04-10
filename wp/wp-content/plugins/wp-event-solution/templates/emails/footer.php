 <!-- Footer Area -->
 <p style="margin-top: 10px; font-size: 14px; color: #556880">
              <?php echo esc_html__( 'Thank you!', 'eventin' ); ?>
            </p>

            <!-- Powered By Eventin -->
            <?php if ( apply_filters( 'eventin_show_email_powered_by', true ) ) : ?>
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #c9c9c9; text-align: center;">
              <p style="margin: 0; font-size: 10px; color: #b0bec5; font-style: italic;">
                <?php esc_html_e( 'Powered by Eventin', 'eventin' ); ?>
              </p>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </body>
</html>
