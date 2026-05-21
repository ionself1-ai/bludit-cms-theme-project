import { useState, useEffect } from "react";
import Navbar from "@/components/Navbar";
import Icon from "@/components/ui/icon";

type Page = "explore" | "categories" | "about" | "blog" | "privacy" | "terms";

const POSTS = [
  {
    id: 1,
    title: "Как запустить сайт за один день без программиста",
    excerpt: "Разбираемся, какие инструменты помогают малому бизнесу выйти в онлайн быстро и без лишних затрат.",
    category: "Бизнес",
    date: "19 мая 2026",
    readTime: "4 мин",
    views: 1240,
    tags: ["no-code", "старт", "веб"],
  },
  {
    id: 2,
    title: "10 советов по написанию контента, который читают до конца",
    excerpt: "Контент без структуры — деньги на ветер. Разбираем приёмы, которые удерживают внимание.",
    category: "Контент",
    date: "17 мая 2026",
    readTime: "6 мин",
    views: 870,
    tags: ["тексты", "SEO", "маркетинг"],
  },
  {
    id: 3,
    title: "Почему минимализм в дизайне работает лучше",
    excerpt: "Меньше — значит больше. Как чистота интерфейса напрямую влияет на конверсию и доверие к бренду.",
    category: "Дизайн",
    date: "14 мая 2026",
    readTime: "5 мин",
    views: 2100,
    tags: ["UI", "UX", "дизайн"],
  },
  {
    id: 4,
    title: "PostgreSQL vs MySQL: что выбрать для своего проекта",
    excerpt: "Честное сравнение двух самых популярных реляционных баз данных для стартапов и малого бизнеса.",
    category: "Технологии",
    date: "11 мая 2026",
    readTime: "7 мин",
    views: 650,
    tags: ["база данных", "backend", "разработка"],
  },
  {
    id: 5,
    title: "Автоматизация рутины: инструменты 2026 года",
    excerpt: "Обзор актуальных no-code и low-code решений, которые реально экономят время каждую неделю.",
    category: "Продуктивность",
    date: "8 мая 2026",
    readTime: "8 мин",
    views: 1870,
    tags: ["автоматизация", "инструменты", "no-code"],
  },
  {
    id: 6,
    title: "Как написать About-страницу, которая продаёт",
    excerpt: "Страница «О нас» — одна из самых посещаемых, но чаще всего самая скучная. Исправляем это.",
    category: "Маркетинг",
    date: "5 мая 2026",
    readTime: "3 мин",
    views: 980,
    tags: ["копирайтинг", "конверсия", "сайт"],
  },
];

const CATEGORIES = [
  { name: "Бизнес", count: 24, icon: "Briefcase", color: "bg-blue-500/10 text-blue-600 dark:text-blue-400" },
  { name: "Дизайн", count: 18, icon: "Palette", color: "bg-violet-500/10 text-violet-600 dark:text-violet-400" },
  { name: "Технологии", count: 31, icon: "Cpu", color: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400" },
  { name: "Маркетинг", count: 15, icon: "TrendingUp", color: "bg-orange-500/10 text-orange-600 dark:text-orange-400" },
  { name: "Продуктивность", count: 12, icon: "Zap", color: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400" },
  { name: "Контент", count: 9, icon: "FileText", color: "bg-pink-500/10 text-pink-600 dark:text-pink-400" },
];

function PostCard({ post }: { post: typeof POSTS[0] }) {
  return (
    <article className="post-card group">
      <div className="flex items-center gap-2 mb-3">
        <span className="badge-tag">{post.category}</span>
        <span className="text-xs text-muted-foreground">{post.date}</span>
        <span className="text-xs text-muted-foreground">· {post.readTime}</span>
      </div>
      <h3 className="font-semibold text-base text-foreground mb-2 leading-snug group-hover:text-foreground/80 transition-colors">
        {post.title}
      </h3>
      <p className="text-sm text-muted-foreground leading-relaxed mb-4 line-clamp-2">
        {post.excerpt}
      </p>
      <div className="flex items-center justify-between">
        <div className="flex gap-1.5 flex-wrap">
          {post.tags.slice(0, 2).map((tag) => (
            <span key={tag} className="text-xs text-muted-foreground hover:text-foreground cursor-pointer transition-colors">
              #{tag}
            </span>
          ))}
        </div>
        <div className="flex items-center gap-1 text-xs text-muted-foreground">
          <Icon name="Eye" size={12} />
          {post.views.toLocaleString()}
        </div>
      </div>
    </article>
  );
}

function ExplorePage() {
  return (
    <div className="py-10">
      <div className="mb-10">
        <div className="flex items-center gap-2 mb-3">
          <span className="inline-flex items-center gap-1.5 text-xs font-medium text-muted-foreground bg-secondary px-2.5 py-1 rounded-full">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
            Свежие материалы
          </span>
        </div>
        <h1 className="text-3xl font-bold tracking-tight text-foreground mb-3">
          Читай. Учись. Развивайся.
        </h1>
        <p className="text-base text-muted-foreground max-w-xl leading-relaxed">
          Авторские материалы о бизнесе, дизайне и технологиях — без воды и шума.
        </p>
      </div>

      <div className="flex items-center gap-2 mb-6 overflow-x-auto pb-1">
        {["Все", "Бизнес", "Дизайн", "Технологии", "Маркетинг", "Продуктивность"].map((f, i) => (
          <button
            key={f}
            className={`flex-shrink-0 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
              i === 0
                ? "bg-foreground text-background"
                : "bg-secondary text-secondary-foreground hover:bg-secondary/80"
            }`}
          >
            {f}
          </button>
        ))}
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {POSTS.map((post) => (
          <PostCard key={post.id} post={post} />
        ))}
      </div>

      <div className="flex justify-center mt-10">
        <button className="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border text-sm font-medium text-foreground hover:bg-secondary transition-colors">
          Загрузить ещё
          <Icon name="ArrowDown" size={14} />
        </button>
      </div>
    </div>
  );
}

function CategoriesPage() {
  return (
    <div className="py-10">
      <h1 className="text-2xl font-bold tracking-tight text-foreground mb-1">Категории</h1>
      <p className="text-sm text-muted-foreground mb-8">Все темы в одном месте</p>
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
        {CATEGORIES.map((cat) => (
          <button
            key={cat.name}
            className="post-card flex items-center gap-4 text-left"
          >
            <div className={`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${cat.color}`}>
              <Icon name={cat.icon} fallback="Folder" size={18} />
            </div>
            <div>
              <p className="font-semibold text-sm text-foreground">{cat.name}</p>
              <p className="text-xs text-muted-foreground">{cat.count} материалов</p>
            </div>
          </button>
        ))}
      </div>

      <div className="mt-10 border border-border rounded-xl p-6 bg-card">
        <h2 className="font-semibold text-foreground mb-4">Популярные теги</h2>
        <div className="flex flex-wrap gap-2">
          {["#no-code", "#SEO", "#UI", "#дизайн", "#маркетинг", "#старт", "#backend", "#автоматизация", "#копирайтинг", "#конверсия", "#инструменты", "#UX"].map((tag) => (
            <span key={tag} className="badge-tag py-1 px-3 text-sm">
              {tag}
            </span>
          ))}
        </div>
      </div>
    </div>
  );
}

function AboutPage() {
  return (
    <div className="py-10 max-w-2xl">
      <div className="flex items-center gap-5 mb-8">
        <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-400 to-violet-500 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
          A
        </div>
        <div>
          <h1 className="text-2xl font-bold text-foreground">Alex Johnson</h1>
          <p className="text-sm text-muted-foreground mt-0.5">Автор · Разработчик · Предприниматель</p>
        </div>
      </div>

      <div className="space-y-4 text-sm text-foreground leading-relaxed">
        <p>
          Привет! Я пишу о пересечении технологий, бизнеса и дизайна. Более 8 лет работаю
          в сфере цифровых продуктов — от стартапов до корпораций.
        </p>
        <p>
          Этот блог — место, где я делюсь практическим опытом без лишней воды. Здесь нет
          корпоративных шаблонов: только реальные кейсы, честные выводы и рабочие инструменты.
        </p>
        <p>
          Если ты предприниматель или специалист, который хочет расти быстрее — ты по адресу.
        </p>
      </div>

      <div className="mt-8 grid grid-cols-3 gap-4">
        {[
          { label: "Статей", value: "120+" },
          { label: "Читателей", value: "14k" },
          { label: "Лет опыта", value: "8" },
        ].map((stat) => (
          <div key={stat.label} className="border border-border rounded-xl p-4 text-center bg-card">
            <p className="text-2xl font-bold text-foreground">{stat.value}</p>
            <p className="text-xs text-muted-foreground mt-0.5">{stat.label}</p>
          </div>
        ))}
      </div>

      <div className="mt-8 flex gap-3">
        <button className="flex items-center gap-2 h-9 px-4 rounded-lg bg-foreground text-background text-sm font-medium hover:opacity-85 transition-opacity">
          <Icon name="Mail" size={14} />
          Написать
        </button>
        <button className="flex items-center gap-2 h-9 px-4 rounded-lg border border-border text-sm font-medium text-foreground hover:bg-secondary transition-colors">
          <Icon name="Twitter" size={14} />
          Twitter
        </button>
      </div>
    </div>
  );
}

function BlogPage() {
  return (
    <div className="py-10">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-foreground">Блог</h1>
          <p className="text-sm text-muted-foreground mt-0.5">Все записи · {POSTS.length} материалов</p>
        </div>
        <div className="flex items-center gap-2">
          <button className="w-8 h-8 flex items-center justify-center rounded-lg border border-border text-muted-foreground hover:text-foreground hover:bg-secondary transition-colors">
            <Icon name="LayoutGrid" size={15} />
          </button>
          <button className="w-8 h-8 flex items-center justify-center rounded-lg bg-secondary text-foreground">
            <Icon name="List" size={15} />
          </button>
        </div>
      </div>

      <div className="space-y-3">
        {POSTS.map((post, i) => (
          <article
            key={post.id}
            className="flex items-start gap-4 p-4 rounded-xl border border-border hover:border-foreground/20 hover:bg-card transition-all cursor-pointer group"
          >
            <span className="text-2xl font-bold text-border group-hover:text-muted-foreground transition-colors w-8 text-center flex-shrink-0 pt-0.5">
              {String(i + 1).padStart(2, "0")}
            </span>
            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-2 mb-1">
                <span className="badge-tag">{post.category}</span>
                <span className="text-xs text-muted-foreground">{post.date} · {post.readTime}</span>
              </div>
              <h3 className="font-semibold text-foreground text-sm leading-snug group-hover:text-foreground/80 transition-colors">
                {post.title}
              </h3>
              <p className="text-xs text-muted-foreground mt-1 line-clamp-1">{post.excerpt}</p>
            </div>
            <div className="flex items-center gap-1 text-xs text-muted-foreground flex-shrink-0">
              <Icon name="Eye" size={12} />
              {post.views.toLocaleString()}
            </div>
          </article>
        ))}
      </div>
    </div>
  );
}

function PrivacyPage() {
  return (
    <div className="py-10 max-w-2xl">
      <div className="flex items-center gap-2 mb-6">
        <Icon name="Shield" size={20} className="text-muted-foreground" />
        <h1 className="text-2xl font-bold text-foreground">Privacy Policy</h1>
      </div>
      <div className="space-y-5 text-sm text-foreground leading-relaxed">
        {[
          { title: "Какие данные мы собираем", text: "Мы собираем только те данные, которые необходимы для работы сервиса: адрес электронной почты при регистрации, технические данные о браузере и устройстве для улучшения работы сайта." },
          { title: "Как используются данные", text: "Данные используются исключительно для персонализации контента и улучшения пользовательского опыта. Мы не продаём и не передаём данные третьим лицам без вашего согласия." },
          { title: "Файлы cookie", text: "Сайт использует cookie для сохранения настроек (например, темы оформления) и аналитики посещаемости. Вы можете отключить cookie в настройках браузера." },
          { title: "Ваши права", text: "Вы имеете право запросить удаление своих данных или экспорт в любой момент. Напишите нам на email в разделе «Обо мне»." },
        ].map((section) => (
          <div key={section.title} className="border border-border rounded-xl p-5 bg-card">
            <h2 className="font-semibold text-foreground mb-2">{section.title}</h2>
            <p className="text-muted-foreground">{section.text}</p>
          </div>
        ))}
      </div>
    </div>
  );
}

function TermsPage() {
  return (
    <div className="py-10 max-w-2xl">
      <div className="flex items-center gap-2 mb-6">
        <Icon name="FileText" size={20} className="text-muted-foreground" />
        <h1 className="text-2xl font-bold text-foreground">Terms of Service</h1>
      </div>
      <div className="space-y-5 text-sm text-foreground leading-relaxed">
        {[
          { title: "Принятие условий", text: "Используя этот сайт, вы соглашаетесь с настоящими условиями. Если вы не согласны с какими-либо положениями, пожалуйста, не используйте сайт." },
          { title: "Использование контента", text: "Все материалы на сайте защищены авторским правом. Вы можете цитировать статьи с обязательной ссылкой на источник. Коммерческое использование без разрешения запрещено." },
          { title: "Ограничение ответственности", text: "Материалы публикуются в информационных целях. Мы не несём ответственности за решения, принятые на основе опубликованного контента." },
          { title: "Изменения условий", text: "Мы оставляем за собой право обновлять условия в любое время. О существенных изменениях мы уведомим через сайт или email." },
        ].map((section) => (
          <div key={section.title} className="border border-border rounded-xl p-5 bg-card">
            <h2 className="font-semibold text-foreground mb-2">{section.title}</h2>
            <p className="text-muted-foreground">{section.text}</p>
          </div>
        ))}
      </div>
    </div>
  );
}

export default function Index() {
  const [theme, setTheme] = useState<"light" | "dark">(() => {
    if (typeof window !== "undefined") {
      return (localStorage.getItem("theme") as "light" | "dark") || "light";
    }
    return "light";
  });
  const [page, setPage] = useState<Page>("explore");

  useEffect(() => {
    const root = document.documentElement;
    if (theme === "dark") {
      root.classList.add("dark");
    } else {
      root.classList.remove("dark");
    }
    localStorage.setItem("theme", theme);
  }, [theme]);

  const toggleTheme = () => setTheme((t) => (t === "light" ? "dark" : "light"));

  const renderPage = () => {
    switch (page) {
      case "explore": return <ExplorePage />;
      case "categories": return <CategoriesPage />;
      case "about": return <AboutPage />;
      case "blog": return <BlogPage />;
      case "privacy": return <PrivacyPage />;
      case "terms": return <TermsPage />;
      default: return <ExplorePage />;
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <Navbar
        theme={theme}
        onToggleTheme={toggleTheme}
        activePage={page}
        onNavigate={(p) => setPage(p as Page)}
      />
      <main>
        <div className="site-container">
          {renderPage()}
        </div>
      </main>

      <footer className="border-t border-border mt-16">
        <div className="site-container py-8">
          <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div className="flex items-center gap-2">
              <div className="w-6 h-6 rounded-md bg-foreground flex items-center justify-center">
                <span className="text-background text-xs font-bold">B</span>
              </div>
              <span className="text-sm font-medium text-foreground">Bludit</span>
            </div>
            <div className="flex items-center gap-4">
              {["Explore", "Блог", "Обо мне"].map((link) => (
                <button
                  key={link}
                  className="text-xs text-muted-foreground hover:text-foreground transition-colors"
                >
                  {link}
                </button>
              ))}
            </div>
            <p className="text-xs text-muted-foreground">© 2026 Bludit</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
