<?php
require_once 'config.php';
$base = APP_URL;
$pageTitle = 'Nosotros';
$pageDesc  = 'Conoce la historia de Katy & Woof, arte con propósito desde La Serena, Chile.';
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh">

  <!-- HERO -->
  <div style="background:var(--dark);padding:5rem 0 4rem;border-bottom:1px solid rgba(255,255,255,0.06);position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 80% 50%,rgba(232,57,154,0.06) 0%,transparent 70%)"></div>
    <div class="container" style="position:relative;z-index:1">
      <div style="max-width:680px">
        <span class="label reveal">Nuestra historia</span>
        <h1 style="margin:1rem 0 1.5rem" class="reveal delay-1">Arte que nace desde<br><span class="accent">el amor y la resiliencia</span></h1>
        <p style="color:var(--mid);font-size:1.05rem;line-height:1.9;max-width:560px" class="reveal delay-2">
          Katy & Woof es más que un emprendimiento. Es una historia de vida, de superación y de gratitud hacia los animales que nos acompañan en los momentos más difíciles.
        </p>
      </div>
    </div>
  </div>

  <!-- HISTORIA -->
  <section class="section">
    <div class="container">
      <div class="grid-2" style="gap:5rem">
        <div>
          <span class="label reveal">La historia</span>
          <h2 style="margin:1rem 0 2rem" class="reveal delay-1">¿Cómo y por qué<br>nace <span class="accent">Katy & Woof</span>?</h2>
          <div style="display:flex;flex-direction:column;gap:1.2rem">
            <p class="reveal delay-2" style="line-height:1.9;color:var(--mid)">
              KATY & WOOF nace desde una historia de amor, resiliencia y conexión con los animales. Durante una etapa muy difícil de mi vida enfrenté <strong style="color:var(--white)">dos cánceres</strong>. En ese proceso, mis mascotas estuvieron siempre a mi lado, acompañándome con su cariño incondicional y convirtiéndose en verdaderos asistentes emocionales que me ayudaron a seguir adelante.
            </p>
            <p class="reveal delay-3" style="line-height:1.9;color:var(--mid)">
              A partir de esa experiencia nació la necesidad de agradecer y honrar el amor de los animales a través del arte. Así comencé a pintar <strong style="color:var(--white)">retratos coloridos de mascotas</strong>, capturando su esencia, su mirada y la alegría que entregan a nuestras vidas.
            </p>
            <p class="reveal delay-4" style="line-height:1.9;color:var(--mid)">
              Con el tiempo, este arte se transformó en KATY & WOOF, un emprendimiento donde los retratos también pueden convertirse en poleras, polerones, mochilas, zapatillas y otros productos personalizados. Además, mi trabajo artístico también incluye <strong style="color:var(--white)">animales autóctonos de Chile</strong>, como una forma de rescatar, valorar y difundir nuestra cultura y la belleza de la fauna de nuestro país.
            </p>
          </div>
        </div>
        <div class="reveal delay-2">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="card" style="aspect-ratio:3/4;background:var(--dark2);display:flex;align-items:center;justify-content:center;color:var(--gray2);grid-row:span 2">
              <div style="text-align:center">
                <i class="fa-solid fa-paw" style="font-size:3rem;opacity:0.3"></i>
                <p style="font-size:0.75rem;margin-top:0.8rem;opacity:0.3">Foto de Katherine</p>
              </div>
            </div>
            <div class="glass" style="padding:1.5rem;display:flex;flex-direction:column;justify-content:center">
              <div style="font-family:'Space Mono',monospace;font-size:2rem;font-weight:700;color:var(--accent)">10+</div>
              <div style="font-size:0.78rem;color:var(--mid);margin-top:0.3rem">Años de experiencia</div>
            </div>
            <div class="glass" style="padding:1.5rem;display:flex;flex-direction:column;justify-content:center">
              <div style="font-family:'Space Mono',monospace;font-size:2rem;font-weight:700;color:var(--accent)">500+</div>
              <div style="font-size:0.78rem;color:var(--mid);margin-top:0.3rem">Retratos creados</div>
            </div>
          </div>
          <div class="glass" style="padding:1.5rem;margin-top:1rem;border-color:rgba(232,57,154,0.15)">
            <div style="font-size:0.75rem;color:var(--accent);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:0.5rem">Fundadora</div>
            <div style="font-size:1.1rem;font-weight:700">Katherine Rojas Labrín</div>
            <div style="font-size:0.82rem;color:var(--mid);margin-top:0.2rem">Artista · La Serena, Chile</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MISIÓN Y VISIÓN -->
  <section class="section" style="background:var(--dark)" id="mision">
    <div class="container">
      <div style="text-align:center;margin-bottom:4rem">
        <span class="label reveal">Propósito</span>
        <h2 style="margin-top:1rem" class="reveal delay-1">Lo que nos <span class="accent">mueve</span></h2>
      </div>
      <div class="grid-2" style="gap:2rem">
        <div class="glass reveal" style="padding:2.5rem;border-color:rgba(232,57,154,0.15)">
          <div style="width:48px;height:48px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(232,57,154,0.2);display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem">
            <i class="fa-solid fa-bullseye" style="color:var(--accent)"></i>
          </div>
          <div style="font-size:0.7rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--accent);margin-bottom:0.8rem;font-family:'Space Mono',monospace">Misión</div>
          <p style="color:var(--light);line-height:1.9;font-size:0.95rem">
            Crear arte que celebre el vínculo entre las personas y los animales, transformando retratos en recuerdos únicos llenos de color, emoción y significado.
          </p>
        </div>
        <div class="glass reveal delay-1" style="padding:2.5rem">
          <div style="width:48px;height:48px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(232,57,154,0.2);display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem">
            <i class="fa-solid fa-eye" style="color:var(--accent)"></i>
          </div>
          <div style="font-size:0.7rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--accent);margin-bottom:0.8rem;font-family:'Space Mono',monospace">Visión</div>
          <p style="color:var(--light);line-height:1.9;font-size:0.95rem">
            Ser una marca reconocida por convertir el amor por los animales en arte con identidad, promoviendo también el valor de la fauna chilena y nuestra cultura.
          </p>
        </div>
      </div>

      <!-- DIFERENCIADORES -->
      <div style="margin-top:3rem">
        <h3 style="text-align:center;margin-bottom:2rem;font-size:1.2rem" class="reveal">¿Qué nos hace <span class="accent">diferentes</span>?</h3>
        <div class="grid-4">
          <?php
          $diffs = [
            ['fa-heart','Historia real','Cada obra nace desde una historia real de resiliencia y amor por los animales.'],
            ['fa-palette','Arte colorido','Retratos únicos, coloridos y llenos de emoción que capturan la personalidad de tu mascota.'],
            ['fa-shirt','Productos únicos','Los retratos se transforman en poleras, polerones, mochilas y accesorios personalizados.'],
            ['fa-leaf','Fauna chilena','Incluimos arte de animales autóctonos de Chile, rescatando nuestra cultura y biodiversidad.'],
          ];
          foreach($diffs as $i => $d): ?>
          <div class="glass reveal" style="padding:1.8rem;transition-delay:<?php echo $i*0.1; ?>s">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(232,57,154,0.2);display:flex;align-items:center;justify-content:center;margin-bottom:1.2rem">
              <i class="fa-solid <?php echo $d[0]; ?>" style="color:var(--accent);font-size:0.9rem"></i>
            </div>
            <h4 style="font-size:0.9rem;margin-bottom:0.5rem"><?php echo $d[1]; ?></h4>
            <p style="font-size:0.82rem;color:var(--mid);line-height:1.7"><?php echo $d[2]; ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- PROCESO -->
  <section class="section" id="proceso">
    <div class="container">
      <div style="text-align:center;margin-bottom:4rem">
        <span class="label reveal">Cómo trabajamos</span>
        <h2 style="margin-top:1rem" class="reveal delay-1">El proceso <span class="accent">paso a paso</span></h2>
      </div>
      <div style="display:flex;flex-direction:column;gap:1.5rem;max-width:720px;margin:0 auto">
        <?php
        $steps = [
          ['01','fa-image','Envío de fotos','Envíanos hasta 3 fotos de tu mascota al correo katy.woof.store@gmail.com o por WhatsApp al +56 9 7688 6481.'],
          ['02','fa-magnifying-glass','Selección de imagen','Elegimos juntos la mejor fotografía para crear el retrato más expresivo de tu mascota.'],
          ['03','fa-paintbrush','Creación del retrato','Realizamos un retrato artístico colorido y personalizado, capturando la esencia única de tu compañero.'],
          ['04','fa-clock','Producción','El retrato se crea en aproximadamente 4 días hábiles con total dedicación y cuidado.'],
          ['05','fa-box-open','Entrega','Recibe tu retrato en formato digital de alta calidad, lienzo físico enviado por correo, o ambas opciones.'],
        ];
        foreach($steps as $i => $s): ?>
        <div class="glass reveal" style="padding:1.8rem;display:flex;gap:1.5rem;align-items:flex-start;transition-delay:<?php echo $i*0.08; ?>s">
          <div style="flex-shrink:0">
            <div style="width:48px;height:48px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(232,57,154,0.2);display:flex;align-items:center;justify-content:center">
              <i class="fa-solid <?php echo $s[1]; ?>" style="color:var(--accent)"></i>
            </div>
          </div>
          <div>
            <div style="font-family:'Space Mono',monospace;font-size:0.65rem;color:var(--accent);letter-spacing:0.2em;margin-bottom:0.4rem"><?php echo $s[0]; ?></div>
            <h4 style="font-size:0.95rem;margin-bottom:0.4rem"><?php echo $s[2]; ?></h4>
            <p style="font-size:0.85rem;color:var(--mid);line-height:1.7"><?php echo $s[3]; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="reveal" style="text-align:center;margin-top:2.5rem">
        <div class="glass" style="display:inline-flex;align-items:center;gap:0.8rem;padding:0.8rem 2rem">
          <i class="fa-regular fa-clock" style="color:var(--accent)"></i>
          <span style="font-size:0.88rem">Tiempo total estimado: <strong style="color:var(--white)">hasta 7 días hábiles</strong></span>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="section-sm" style="background:var(--dark);border-top:1px solid rgba(255,255,255,0.06)">
    <div class="container" style="text-align:center">
      <h2 style="margin-bottom:1.5rem" class="reveal">¿Lista para crear tu <span class="accent">retrato</span>?</h2>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap" class="reveal delay-1">
        <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-primary btn-lg">Ver Catálogo</a>
        <a href="<?php echo $base; ?>/contacto.php" class="btn btn-outline btn-lg">Contactar</a>
      </div>
    </div>
  </section>

</main>

<?php include 'footer.php'; ?>
