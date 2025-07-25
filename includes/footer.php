    <footer class="footer-principal">
        <div class="contenedor">
            <div class="footer-contenido">
                <div class="footer-seccion">
                    <h3>Figger Energy SAS</h3>
                    <p>Entidad gubernamental colombiana especializada en el monitoreo y control de actividades de minería ilegal que extraen materiales críticos para energías renovables.</p>
                </div>
                
                <div class="footer-seccion">
                    <h4>Enlaces Oficiales</h4>
                    <ul>
                        <li><a href="<?php echo $ruta_base ?? ''; ?>index.php">Página Principal</a></li>
                        <li><a href="<?php echo $ruta_base ?? ''; ?>views/public/contact.php">Contacto Institucional</a></li>
                        <li><a href="#" onclick="alert('Portal en desarrollo')">Portal de Transparencia</a></li>
                        <li><a href="<?php echo $ruta_base ?? ''; ?>politica-privacidad.php">Política de Privacidad</a></li>
                    </ul>
                </div>
                
                <div class="footer-seccion">
                    <h4>Información Legal</h4>
                    <ul>
                        <li><strong>NIT:</strong> 900.123.456-7</li>
                        <li><strong>Código DANE:</strong> 11001</li>
                        <li><strong>Dirección:</strong><br>
                            Calle 1, Carrera 1, Edificio 1<br>
                            Macondo, Colombia</li>
                        <li><strong>Horario de Atención:</strong><br>
                            Lunes a Viernes: 8:00 AM - 5:00 PM</li>
                        <li><strong>Línea de Atención:</strong><br>
                            +57 300 0000 000</li>
                    </ul>
                </div>
                
                <div class="footer-seccion">
                    <h4>Contacto de Emergencia</h4>
                    <p><strong>Reporte de Minería Ilegal:</strong></p>
                    <p>📞 Línea directa: +57 018000001</p>
                    <p>📧 Email: emergencias@figgerenergy.gov.co</p>
                    <p><strong>Disponible 24/7</strong></p>
                </div>
            </div>
            
            <div class="footer-inferior">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> Figger Energy SAS - Gobierno de Colombia. Todos los derechos reservados.</p>
                </div>
                
                <div class="footer-sellos">
                    <small>Portal desarrollado bajo estándares de gobierno digital</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Incluir JavaScript básico -->
    <script src="<?php echo $ruta_base ?? ''; ?>assets/js/scripts.js"></script>
</body>
</html>
